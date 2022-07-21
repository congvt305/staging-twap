<?php
declare(strict_types=1);

namespace Eguana\OrderDeliveryMessage\Plugin\Checkout\Model;

use Amasty\CheckoutCore\Model\Config;
use Magento\Framework\Escaper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class GuestAddDataDeliveryMessage
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param Config $amastyConfig
     * @param QuoteRepository $quoteRepository
     * @param Escaper $escaper
     */
    public function __construct(
        Config $amastyConfig,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        QuoteRepository $quoteRepository,
        Escaper $escaper
    ) {
        $this->amastyConfig = $amastyConfig;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->quoteRepository = $quoteRepository;
        $this->escaper = $escaper;
    }

    /**
     * Add delivery message in case one page checkout
     *
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
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

            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
            $quote->setDeliveryMessage($deliveryMessage);
            $this->quoteRepository->save($quote);
        }
    }
}
