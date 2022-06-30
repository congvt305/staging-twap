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
use Atome\MagentoPayment\Model\PaymentGateway;
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

class Cancel extends MyCsrfAwareActionAction
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
    $this->commonHelper->logInfo('action Cancel: begin');
    $queryParams = $this->getRequest()->getParams();
    $this->commonHelper->logInfo('action Cancel: queryParams => ' . json_encode($queryParams));
    $orderId = $queryParams['orderId'] ?? null;
    if ($orderId && $this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
      try {
        $order = $this->callbackHelper->getOrderByOrderId($orderId);
        if (empty($order)) {
          throw new Exception("no this order: ". $orderId);
        }
        $payment = $order->getPayment();
        if ($payment->getMethod() !== PaymentGateway::METHOD_CODE) {
          throw new Exception("Wrong payment gateway: " . $payment->getMethod());
        }
        if ($order->getStatus() !== \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
          throw new Exception("Wrong order status: " . $order->getStatus());
        }
        $order->cancel();
        $order->save();
        if ($this->paymentGatewayConfig->getDeleteOrdersWithoutPaying()) {
          $registry = $this->_objectManager->get('Magento\Framework\Registry');
          $registry->register('isSecureArea', true, true);
          $order->delete();
          $registry->unregister('isSecureArea');
        }
      } catch (LocalizedException $e) {
        $this->commonHelper->logError('action cancel LocalizedException: ' . $e->getMessage());
        if ($e->getCode() !== CommonHelper::EXP_CODE_PAYMENT_PROCESSING) {
          $error = $e->getMessage();
        }
      } catch (Exception $e) {
        $this->commonHelper->logError("action cancel : " . $e->getMessage());
        $error = get_class($e) . ': ' . $e->getMessage();
      }
    } else {
      $error = "unsupported payment action";
    }

    $resp = $this->jsonFactory->create();
    $respData = [];
    $respData['orderId'] = $orderId;
    
    if (!empty($error)) {
      $respData['error'] = true;
      $respData['message'] = $error;
    } else {
      $respData['deleted'] = true;
    }
    $resp->setData($respData);
    $this->commonHelper->logInfo('action Cancel: respData => ' . json_encode($respData));
    return $resp;
  }
}
