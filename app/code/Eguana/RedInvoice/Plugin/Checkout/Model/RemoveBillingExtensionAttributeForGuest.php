<?php
declare(strict_types=1);

namespace Eguana\RedInvoice\Plugin\Checkout\Model;

use Amasty\CheckoutCore\Model\Config;
use Eguana\RedInvoice\Model\RedInvoiceLogger;
use Magento\Checkout\Model\Session;

class RemoveBillingExtensionAttributeForGuest
{
    /**
     * @var Config
     */
    private $amastyConfig;

    /**
     * @param Config $amastyConfig
     */
    public function __construct(
        Config $amastyConfig
    ) {
        $this->amastyConfig = $amastyConfig;
    }

    /**
     * Add red invoice in case one page checkout
     *
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->amastyConfig->isEnabled()) {
            //must remove extension attribute so don't get error when move to Magento\CheckoutStaging\Plugin\GuestPaymentInformationManagementPlugin:106
            $billingAddress->setData('extension_attributes', null);
        }
    }
}
