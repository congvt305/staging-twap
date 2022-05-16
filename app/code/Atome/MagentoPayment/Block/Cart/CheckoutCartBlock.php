<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Block\Cart;

use Atome\MagentoPayment\Model\PaymentGateway;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Model\Config\LocaleConfig;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;

class CheckoutCartBlock extends Template
{
    protected $paymentGatewayConfig;
    protected $localeConfig;
    protected $paymentGateway;
    protected $checkoutSession;

    public function __construct(
        Template\Context $context,
        PaymentGatewayConfig $paymentGatewayConfig,
        LocaleConfig $localeConfig,
        PaymentGateway $paymentGateway,
        CheckoutSession $checkoutSession,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->localeConfig = $localeConfig;
        $this->paymentGateway = $paymentGateway;
        $this->checkoutSession = $checkoutSession;
    }

    public function canShow()
    {
        if (!$this->paymentGatewayConfig->isActive()) {
            return false;
        }
        $quote = $this->checkoutSession->getQuote();
        if (!$this->paymentGateway->canUseForCurrencyAmount($quote->getQuoteCurrencyCode(), $quote->getGrandTotal())) {
            return false;
        }
        $products = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $products[] = $item->getProduct();
        }
        return $this->paymentGateway->canUseForProducts($products);
    }

    public function supportNewUserOff()
    {        
        return $this->localeConfig->getNewUserOff(false);
    }

    public function getFormatedNewUserOffAmount()
    {
        switch ($this->localeConfig->getNewUserOffType() ?: 'AMOUNT') {
            case 'PERCENTAGE':
                return $this->localeConfig->getNewUserOffAmount() . '%';
                break;
            case 'AMOUNT':
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $newUserOffAmount = intval($this->localeConfig->getNewUserOffAmount(0) / 100);
                return $objectManager->create('\Magento\Framework\Pricing\PriceCurrencyInterface')->format($newUserOffAmount, true, 0);
                break;
            default:
                return '';
                break;
        }
    }
}
