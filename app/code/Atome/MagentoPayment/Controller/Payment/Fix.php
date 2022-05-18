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
use Magento\Framework\App\ObjectManager;
use Atome\MagentoPayment\Model\PaymentGateway;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

// CsrfAwareActionInterface is for magento 2.3 and later, see: https://magento.stackexchange.com/questions/253414/magento-2-3-upgrade-breaks-http-post-requests-to-custom-module-endpoint
if (interface_exists("Magento\Framework\App\CsrfAwareActionInterface")) {
  require_once __DIR__ . '/MyCsrfAwareActionAction.interface.php-sub'; // we must NOT use ".php" extension here, or the setup:di:compile will try to parse the file, and old php parser reports error
} else {
  require_once __DIR__ . '/MyCsrfAwareActionAction.dummy.php-sub';
}

class Fix extends MyCsrfAwareActionAction
{
  protected $commonHelper;
  protected $checkoutSession;
  protected $paymentGatewayConfig;
  protected $callbackHelper;
  protected $controllerContext;
  protected $jsonFactory;

  public function __construct(
    Context $context,
    CommonHelper $commonHelper,
    PaymentGatewayConfig $paymentGatewayConfig,
    CallbackHelper $callbackHelper,
    JsonFactory $jsonFactory
  ) {
    parent::__construct($context);
    $this->controllerContext = $context;
    $this->commonHelper = $commonHelper;
    $this->callbackHelper = $callbackHelper;
    $this->paymentGatewayConfig = $paymentGatewayConfig;
    $this->jsonFactory = $jsonFactory;
  }

  public function execute()
  {
    $queryParams = $this->getRequest()->getParams();
    $resp = $this->jsonFactory->create();

    $authStr = $queryParams['authStr'] ?? '';
    $auth = base64_decode($authStr);
    if ($auth !== (trim($this->paymentGatewayConfig->getMerchantApiKey()) . ':' . trim($this->paymentGatewayConfig->getMerchantApiSecret()))) {
      $resp->setData([
        'message' => 'Auth Failed!',
        'authStr' => $authStr,
        'auth' => $auth,
      ]);
      return $resp;
    }

    $quoteId = $queryParams['quoteId'] ?? null;
    $orderId = $queryParams['orderId'] ?? null;
    try {
      if ($this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
        $order = $this->callbackHelper->getOrderByOrderId($orderId) || $this->callbackHelper->getOrderByIncrementId($orderId);
        if (!$order) {
          $resp->setData(['message' => 'order is null']);
          return $resp;
        }
        $payment = $order->getPayment();
        if (!$payment) {
          $resp->setData(['message' => 'order payment is null']);
          return $resp;
        }
        if ($payment->getMethod() !== PaymentGateway::METHOD_CODE) {
          $resp->setData(['message' => 'order payment method is not atome, is: ' . $payment->getMethod()]);
          return $resp;
        }
      } else {
        $quote = ObjectManager::getInstance()->create('Magento\Quote\Model\Quote')->loadByIdWithoutStore($quoteId);
        if (!$quote) {
          $resp->setData(['message' => 'quote is null']);
          return $resp;
        }
        $payment = $quote->getPayment();
        if (!$payment) {
          $resp->setData(['message' => 'quote payment is null']);
          return $resp;
        }
        if ($payment->getMethod() !== PaymentGateway::METHOD_CODE) {
          $resp->setData(['message' => 'quote payment method is not atome, is: ' . $payment->getMethod()]);
          return $resp;
        }
      }

      $action = $queryParams['action'] ?? 'query';
      if ('update' === $action) {
        if (isset($queryParams['additionalInformation'])) {
          $respData = [
            'originAdditionalInformation' => $payment->getAdditionalInformation(),
          ];

          $addtionalInformation = json_decode($queryParams['additionalInformation'], true);
          if (is_array($addtionalInformation)) {
            $payment->setAdditionalInformation($addtionalInformation);
          }
          $payment->save();
          $respData['currentAdditionalInformation'] = $payment->getAdditionalInformation();

          $resp->setData($respData);
        } else if (isset($queryParams['billingCustomerAddressId']) && $quote) {
          $respData = [
            'originBillingAddress' => $quote->getBillingAddress()->getData(),
          ];

          $quote->getBillingAddress()->setCustomerAddressId($queryParams['billingCustomerAddressId'] ?: null);
          $quote->save();

          $respData['currentBillingAddress'] = $quote->getBillingAddress()->getData();

          $resp->setData($respData);
        } else if (isset($queryParams['shippingCustomerAddressId']) && $quote) {
          $respData = [
            'originShippingAddress' => $quote->getShippingAddress()->getData(),
          ];

          $quote->getShippingAddress()->setCustomerAddressId($queryParams['shippingCustomerAddressId'] ?: null);
          $quote->save();

          $respData['currentShippingAddress'] = $quote->getShippingAddress()->getData();

          $resp->setData($respData);
        }
      } else if ('query' === $action) {
        $resp->setData([
          'additionalInformation' => $payment->getAdditionalInformation(),
          'quote' => $quote ? $quote->getData() : null,
          'billingAddress' => $quote ? $quote->getBillingAddress()->getData() : null,
          'shippingAddress' => $quote ? $quote->getShippingAddress()->getData() : null,
        ]);
      }

      return $resp;
    } catch (Exception $e) {
      $resp->setData([
        'error' => get_class($e) . ': ' . $e->getMessage(),
        'debug_trace' => $e->getTrace(),
      ]);
      return $resp;
    }
  }
}
