<?php
declare(strict_types=1);

namespace CJ\Checkout\Plugin\Model\Quote\Item;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator as QuantityValidatorAlias;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 * Class QuantityValidator
 */
class QuantityValidator {
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     */
    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper
    ) {
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * Add error items data to quote
     *
     * @param QuantityValidatorAlias $subject
     * @param $result
     * @param Observer $observer
     * @return mixed
     */
    public function afterValidate(QuantityValidatorAlias $subject, $result, Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        if ($quoteItem->getHasError() && !$quoteItem->getParentItem() && $this->promoItemHelper->isPromoItem($quoteItem)) {
            $quote = $quoteItem->getQuote();
            $errorItem = $quote->getErrorItems();
            if (!$errorItem || !is_array($errorItem)) {
                $errorItem = [];
            }
            if ($quoteItem->getId()) {
                $errorItem[] = $quoteItem;
                $quote->setErrorItems($errorItem);
                $quote->setData('has_error', true);
            }
        }
        return $result;
    }
}
