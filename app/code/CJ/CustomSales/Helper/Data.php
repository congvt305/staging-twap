<?php

namespace CJ\CustomSales\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Amasty\Promo\Helper\Item as PromoHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var PromoHelper
     */
    protected $promoHelper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param PromoHelper $promoHelper
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository,
        PromoHelper $promoHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->promoHelper = $promoHelper;
        parent::__construct($context);
    }

    /**
     * @param $rule
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemsValidForRule($rule)
    {
        $itemsValidQty = 0;
        $quoteId = $this->checkoutSession->getQuoteId();
        if ($quoteId) {
            $quote = $this->cartRepository->get($quoteId);
            $quoteItems = $quote->getItems();
            foreach ($quoteItems as $item) {
                $isValid = $rule->getActions()->validate($item);
                if ($isValid && !$this->promoHelper->isPromoItem($item)) {
                    $itemsValidQty += $item->getQty();
                }
            }
        }

        return $itemsValidQty;
    }

    /**
     * @param $rule
     * @return array|string[]
     */
    public function getExcludeSkusOfRule($rule)
    {
        $exludeSkus = [];
        if ($rule->getData("exclude_skus")) {
            $exludeSkus = explode(",", $rule->getData("exclude_skus"));
        }
        return $exludeSkus;
    }
}