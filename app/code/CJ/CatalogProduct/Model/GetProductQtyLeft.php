<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Model;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetProductQtyLeft
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private GetProductSalableQtyInterface $getProductSalableQty;

    /**
     * @var StockResolverInterface
     */
    private StockResolverInterface $stockResolver;

    /**
     * @var Configuration
     */
    private Configuration $catalogConfig;

    /**
     * @var GetBundleMinQty
     */
    private GetBundleMinQty $getBundleMinQty;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockResolverInterface $stockResolver
     * @param Configuration $catalogConfig
     * @param GetBundleMinQty $getBundleMinQty
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockResolverInterface $stockResolver,
        Configuration $catalogConfig,
        GetBundleMinQty $getBundleMinQty,
        StoreManagerInterface $storeManager
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockResolver = $stockResolver;
        $this->catalogConfig = $catalogConfig;
        $this->getBundleMinQty = $getBundleMinQty;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $product
     * @param $requestQty
     * @param $bundleOptions
     * @return array
     */
    public function execute($product, $requestQty, $bundleOptions = [])
    {
        $result = [
            'message' => '',
            'is_in_stock' => true
        ];

        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

            $salableQty = $product->getTypeId() == 'bundle'
                ? $this->getBundleMinQty->execute($product, $stockId, $bundleOptions)
                : $this->getProductSalableQty->execute($product->getSku(), (int)$stockId);

            if (0 >= $salableQty) {
                throw new LocalizedException(__('This product is out of stock.'));
            }

            $qtyLeft = $salableQty - (float) $requestQty;
        } catch (LocalizedException $e) {
            $result['is_in_stock'] = false;
            $result['message'] = $e->getMessage();

            return $result;
        }

        $stockThresholdQty = $this->catalogConfig->getStockThresholdQty();
        if ($stockThresholdQty >= $qtyLeft) {
            $result['message'] = __("Hurry! Only %1 Left", $salableQty);
        }

        if (0 >= $qtyLeft) {
            $result['is_in_stock'] = false;
            $result['message'] = __('The requested qty is not available');
        }

        return $result;
    }
}
