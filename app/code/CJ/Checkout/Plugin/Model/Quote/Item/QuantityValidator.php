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

    const STORE_TW_SULWHASOO_CODE = 'default';

    const STORE_TW_LANEIGE_CODE = 'tw_laneige';
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->storeManager = $storeManager;
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
        $storeCode = $this->storeManager->getStore()->getCode();
        if (($storeCode == self::STORE_TW_LANEIGE_CODE || $storeCode == self::STORE_TW_SULWHASOO_CODE) &&
            ($quoteItem->getHasError() && !$quoteItem->getParentItem() && $this->promoItemHelper->isPromoItem($quoteItem))
        ) {

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
