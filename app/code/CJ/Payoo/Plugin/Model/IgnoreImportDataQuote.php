<?php
declare(strict_types=1);

namespace CJ\Payoo\Plugin\Model;

use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;

class IgnoreImportDataQuote
{
    const STORE_CODE_VN_LANEIGE = 'vn_laneige';

    const PAYNOW_VISA = 'paynow-visa';

    const PAYNOW_WALLET = 'paynow-wallet';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QuoteRepository $quoteRepository
    ) {
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Ignore import data for case payoo-visa and payoo-wallet
     *
     * @param \Magento\Quote\Model\Quote\Payment $payment
     * @param callable $proceed
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSet(
        \Magento\Quote\Model\PaymentMethodManagement $payment,
        callable $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $method
    ) {
        //Because we don't create new method for paynow-visa and paynow-wallet so must ignore to avoid error when click choose payment method on front end
        if (($method->getMethod() == self::PAYNOW_VISA || $method->getMethod() == self::PAYNOW_WALLET) &&
            $this->storeManager->getStore()->getCode() == self::STORE_CODE_VN_LANEIGE
        ) {
            $quote = $this->quoteRepository->get($cartId);
            $payment = $quote->getPayment();
            $payment->setMethod($method->getMethod());
            $quote->save();
            return $this;
        } else {
            return $proceed($cartId, $method);
        }
    }
}
