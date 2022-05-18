<?php

/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */

namespace Atome\MagentoPayment\Controller\Payment;

use Atome\MagentoPayment\Helper\CallbackHelper;
use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;

// CsrfAwareActionInterface is for magento 2.3 and later, see: https://magento.stackexchange.com/questions/253414/magento-2-3-upgrade-breaks-http-post-requests-to-custom-module-endpoint
if (interface_exists("Magento\Framework\App\CsrfAwareActionInterface")) {
    require_once __DIR__ . '/MyCsrfAwareActionAction.interface.php-sub'; // we must NOT use ".php" extension here, or the setup:di:compile will try to parse the file, and old php parser reports error
} else {
    require_once __DIR__ . '/MyCsrfAwareActionAction.dummy.php-sub';
}

class Callback extends MyCsrfAwareActionAction
{
    protected $commonHelper;
    protected $checkoutSession;
    protected $paymentGatewayConfig;
    protected $callbackHelper;
    protected $controllerContext;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        CommonHelper $commonHelper,
        PaymentGatewayConfig $paymentGatewayConfig,
        CallbackHelper $callbackHelper,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->controllerContext = $context;
        $this->commonHelper = $commonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->callbackHelper = $callbackHelper;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $this->commonHelper->logInfo('action Callback: begin');
        $queryParams = $this->getRequest()->getParams();
        $this->commonHelper->logInfo('action Callback: queryParams => ' . json_encode($queryParams));
        $quoteId = $queryParams['quoteId'] ?? null;
        $orderId = $queryParams['orderId'] ?? null;
        if ($this->paymentGatewayConfig->getPaymentAction() == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
            try {
                if ($this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
                    $ctx = $this->callbackHelper->applyOrderPayment($orderId, $queryParams);
                } else {
                    $ctx = $this->callbackHelper->applyQuotePayment($quoteId, $queryParams);
                }
            } catch (LocalizedException $e) {
                $this->commonHelper->logError('action Callback applyQuotePayment LocalizedException: ' . $e->getMessage());
                if ($e->getCode() !== CommonHelper::EXP_CODE_PAYMENT_PROCESSING) {
                    $error = $e->getMessage();
                }
            } catch (Exception $e) {
                $this->commonHelper->logError("action Callback applyQuotePayment: " . $e->getMessage());
                $error = get_class($e) . ': ' . $e->getMessage();
            }
        } else {
            $error = "unsupported payment action";
        }

        $resp = $this->jsonFactory->create();
        $respData = ['quoteId' => $quoteId];
        $respData['orderId'] = $orderId;
        if (isset($ctx)) {
            $respData['reservedOrderId'] = $ctx->quote ? $ctx->quote->getReservedOrderId() : ($ctx->orderCreated ? $ctx->orderCreated->getIncrementId() : '');
            $respData['isOrderCreated'] = !!$ctx->orderCreated;
        }
        if (!empty($error)) {
            $resp->setHttpResponseCode(400);
            $respData['error'] = true;
            $respData['message'] = $error;
        }
        $resp->setData($respData);
        $this->commonHelper->logInfo('action Callback: respData => ' . json_encode($respData));
        return $resp;
    }
}
