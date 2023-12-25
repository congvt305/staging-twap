<?php
declare(strict_types=1);

namespace Eguana\OrderDeliveryMessage\Plugin\Checkout\Model;

use Amasty\CheckoutCore\Model\Config;
use Magento\Framework\Escaper;
use Magento\Quote\Model\QuoteRepository;

class AddDataDeliveryMessage
{
    /**
     * @var Config
     */
    private $amastyConfig;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Config $amastyConfig
     * @param QuoteRepository $quoteRepository
     * @param Escaper $escaper
     */
    public function __construct(
        Config $amastyConfig,
        QuoteRepository $quoteRepository,
        Escaper $escaper
    ) {
        $this->amastyConfig = $amastyConfig;
        $this->quoteRepository = $quoteRepository;
        $this->escaper = $escaper;
    }

    /**
     * Add delivery message in case one page checkout
     *
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->amastyConfig->isEnabled()) {
            $extAttributes = $billingAddress->getData('extension_attributes');
            if (!isset($extAttributes)) {
                return;
            }
            $deliveryMessage = $extAttributes->getDeliveryMessage();
            /**
             * By Abbas I am using strip_tags because I did not get any related function in Magento 2
             * In core files they are also using it. For example at
             * vendor/magento/module-catalog/Model/Product/Option/Type/File.php Line 407
             */
            $deliveryMessage = $this->escaper->escapeHtml(strip_tags($deliveryMessage));
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setDeliveryMessage($deliveryMessage);
            $this->quoteRepository->save($quote);
        }
    }
}
