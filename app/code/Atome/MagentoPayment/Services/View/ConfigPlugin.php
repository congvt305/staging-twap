<?php

namespace Atome\MagentoPayment\Services\View;

use Atome\MagentoPayment\Services\Config\Atome;
use Atome\MagentoPayment\Services\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Services\Payment\API\AuthenticationRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

class ConfigPlugin
{
    protected $paymentGatewayConfig;

    public function __construct(PaymentGatewayConfig $paymentGatewayConfig)
    {
        $this->paymentGatewayConfig = $paymentGatewayConfig;
    }

    public function aroundSave(\Magento\Config\Model\Config $subject, \Closure $proceed)
    {
        $result = $proceed();

        try {
            $store = ObjectManager::getInstance()->create(StoreManagerInterface::class);
            $request = new AuthenticationRequest();
            $request->setCountry($this->paymentGatewayConfig->getCountry())
                ->setCallbackUrl("{$store->getStore()->getBaseUrl()}atome/payment/ping")
                ->setConfiguration([
                    'version' => Atome::version(),
                    'enabled' => $this->paymentGatewayConfig->isActive(),
                    'country' => $this->paymentGatewayConfig->getCountry(),
                    'environment' => $this->paymentGatewayConfig->getApiEnv(),
                    'language' => $this->paymentGatewayConfig->getLanguage(),
                    'skuPermission' => true,
                    'priceDividerProductList' => $this->paymentGatewayConfig->isPriceDividerProductDetailVisible(),
                    'priceDividerProductDetail' => $this->paymentGatewayConfig->isPriceDividerProductDetailVisible(),
                    'minimumAmount' => (float)$this->paymentGatewayConfig->getMinSpend(),
                    'maximumAmount' => (float)$this->paymentGatewayConfig->getMaxSpend(),
                    'cancelTimeout' => (int)$this->paymentGatewayConfig->getCancelTimeout(),
                    'debugMode' => $this->paymentGatewayConfig->isDebugEnabled(),
                    'atomeCustomStatus' => $this->paymentGatewayConfig->getOrderStatus(),
                    'clearCartWithoutPaying' => (boolean)$this->paymentGatewayConfig->getClearCartWithoutPaying(),
                    'newOrderStatus' => $this->paymentGatewayConfig->getNewOrderStatus(),
                    'orderEmailSentBy' => $this->paymentGatewayConfig->getOrderEmailSendBy(),
                    'excludeCategory' => json_encode($this->paymentGatewayConfig->getExcludedCategories() ?: []),
                    'sortOrder' => (int)$this->paymentGatewayConfig->getSortOrder(),
                ])
                ->request();
        } catch (Throwable $t) {
            $message = ObjectManager::getInstance()->create(ManagerInterface::class);
            $message->addErrorMessage("Api Key or Api Password is entered incorrectly or an error occurs during the Atome callback.");
            $message->addErrorMessage($t->getMessage());
        }

        return $result;
    }
}
