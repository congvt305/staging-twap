<?php

namespace CJ\ProductInventoryQty\Helper;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package CJ\ProductInventoryQty\Helper
 */
class Data extends AbstractHelper
{
    const TYPE_SIMPLE = 'simple';
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private GetSourceItemsBySkuInterface $sourceItemsBySku;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param GetSourceItemsBySkuInterface $sourceItemsBySku
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        GetSourceItemsBySkuInterface $sourceItemsBySku
    ) {
        $this->storeManager = $storeManager;
        $this->sourceItemsBySku = $sourceItemsBySku;

        parent::__construct($context);
    }

    /**
     * @param $sku
     * @return float
     */
    public function getStockQty($sku, $type)
    {
        $qty = 0;
        if ($type == self::TYPE_SIMPLE) {
            $sourceItemList = $this->sourceItemsBySku->execute($sku);
            foreach ($sourceItemList as $source) {
                if ($source->getStatus() == 1) {
                    $qty += $source->getQuantity();
                }
            }
        }

        return $qty;
    }
}
