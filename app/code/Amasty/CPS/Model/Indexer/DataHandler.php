<?php

namespace Amasty\CPS\Model\Indexer;

use Amasty\CPS\Model\Product\Sorting;
use Amasty\CPS\Model\ResourceModel\BrandProduct;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Amasty\ShopbyBase\Helper\FilterSetting;

class DataHandler implements \Amasty\CPS\Api\Indexer\DataHandlerInterface
{
    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var BrandProduct
     */
    private $brandProduct;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    private $iterator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var Sorting
     */
    private $sorting;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSettingHelper;

    public function __construct(
        \Amasty\CPS\Model\ResourceModel\BrandProduct $brandProduct,
        \Amasty\ShopbyBrand\Helper\Data $config,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Amasty\CPS\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        Repository $attributeRepository,
        Sorting $sorting,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSettingHelper
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->brandProduct = $brandProduct;
        $this->iterator = $iterator;
        $this->productRepository = $productRepository;
        $this->appState = $appState;
        $this->attributeRepository = $attributeRepository;
        $this->sorting = $sorting;
        $this->optionSettingHelper = $optionSettingHelper;
    }

    /**
     * @return $this|\Amasty\CPS\Api\Indexer\DataHandlerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reindexAll()
    {
        $brandAttribute = $this->config->getBrandAttributeCode();
        if (!$brandAttribute) {
            return $this;
        }

        $storeIds = $this->getStoreIds();
        $options = $this->attributeRepository->get($brandAttribute)->getOptions();
        foreach ($options as $brand) {
            $brandId = $brand->getValue();
            if ($brandId) {
                foreach ($storeIds as $storeId) {
                    $this->appState->emulateAreaCode(
                        \Magento\Framework\App\Area::AREA_FRONTEND,
                        [$this, 'updateBrand'],
                        [$brandId, $storeId, $brandAttribute]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getStoreIds()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        return $storeIds;
    }

    /**
     * @param array $products
     */
    public function reindexByProduct($products = [])
    {
        $storeIds = $this->getStoreIds();
        $brandAttribute = $this->config->getBrandAttributeCode();
        if (!$brandAttribute) {
            return;
        }

        foreach ($storeIds as $storeId) {
            $productCollection = $this->productCollectionFactory->create()
                ->addStoreFilter($storeId)
                ->addAttributeToSelect($brandAttribute, 'inner');
            if ($products) {
                $productCollection->addIdFilter($products);
            }
            $this->updateProducts($brandAttribute, $storeId, $productCollection->getItems());
        }
    }

    /**
     * @param string $brandAttribute
     * @param string|int $storeId
     * @param array $products
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateProducts(string $brandAttribute, $storeId, $products = [])
    {
        foreach ($products as $product) {
            $id = $product['entity_id'];
            $brands = explode(',', $product[$brandAttribute]);
            $currentBrands = $this->getProductBrands($id, $storeId);

            if ($diff = array_diff($currentBrands, $brands)) {
                $this->brandProduct->clearBrandData([
                    'products' => $id,
                    'stores' => [$storeId],
                    'brands' => $diff
                ]);
            }

            if ($diff = array_diff($brands, $currentBrands)) {
                $this->callbackUpdateProduct(
                    [
                        'row' => [
                            'entity_id' => $id,
                            $brandAttribute => $diff,
                        ],
                        'brandAttribute' => $brandAttribute,
                        'store_id' => $storeId
                    ]
                );
            }
        }
    }

    /**
     * @param int $id
     * @param string|int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductBrands($id, $storeId)
    {
        $brandData = $this->brandProduct->getBrandIdsByProductIds($id, $storeId);

        return isset($brandData[$id]) ? array_keys($brandData[$id]) : [];
    }

    /**
     * @param array $args
     */
    public function callbackUpdateProduct($args)
    {
        $row = $args['row'];
        foreach ($row[$args['brandAttribute']] as $brand) {
            $positions = $this->brandProduct->getProductPositionData($brand, $args['store_id']);
            $position = !empty($positions) ? max($positions) + 1 : 0;

            $this->brandProduct->updateProductPositionsByBrand(
                $brand,
                $args['store_id'],
                [$row['entity_id'] => $position]
            );
        }
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @param string $brandAttribute
     */
    public function updateBrand(int $brandId, int $storeId, string $brandAttribute)
    {
        $productCollection = $this->getCollectionForIndex($brandId, $storeId, $brandAttribute);

        $productIds = $productCollection->getAllIds();
        $pinnedProductPositionData = $this->brandProduct->getProductPositionData($brandId, $storeId, true);
        $productIds = $this->sortIds($productIds, $pinnedProductPositionData);

        $this->brandProduct->clearBrandData([
            'brands' => [$brandId],
            'stores' => [$storeId]
        ]);
        if ($productIds) {
            $this->brandProduct->updateProductPositionsByBrand(
                $brandId,
                $storeId,
                array_flip($productIds),
                array_keys($pinnedProductPositionData)
            );
        }
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @param string $brandAttribute
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getCollectionForIndex(int $brandId, int $storeId, string $brandAttribute)
    {
        $productCollection = $this->productCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->setStore($storeId);
        if ($storeId) {
            $productCollection->addPriceData();
        } else {
            $productCollection->addAttributeToSelect('price', true);
        }
        $productCollection
            ->addAttributeToFilter('visibility', ['IN' =>$this->productVisibility->getVisibleInSiteIds()])
            ->addAttributeToFilter($brandAttribute, ['finset' => $brandId]);
        $settingModel = $this->optionSettingHelper->getSettingByValue(
            $brandId,
            $brandAttribute,
            $storeId
        );
        if ($settingModel->getSorting()) {
            $this->sorting->applySorting($productCollection, $settingModel->getSorting());
        }

        return $productCollection;
    }

    /**
     * @param array $productIds
     * @param array $pinnedProductIds
     * @return array
     */
    private function sortIds($productIds, $pinnedProductIds)
    {
        $sorted = $this->preparePositionDataForSort($productIds, $pinnedProductIds);
        $productIds = array_diff($productIds, $sorted);
        $itemsCount = count($productIds) + count($sorted);
        $idx = 0;
        while ($idx < $itemsCount) {
            if (!isset($sorted[$idx]) && current($productIds)) {
                $sorted[$idx] = current($productIds);
                next($productIds);
            }
            $idx++;
        }

        ksort($sorted, SORT_NUMERIC);
        return $sorted;
    }

    /**
     * @param array $productIds
     * @param array $pinnedProductIds
     * @return array
     */
    private function preparePositionDataForSort($productIds, $pinnedProductIds)
    {
        $positionData = array_intersect(array_flip($pinnedProductIds), $productIds);
        krsort($positionData);
        $maxPosition = count($productIds) - 1;
        foreach ($positionData as $position => $productId) {
            if ($position > $maxPosition) {
                $positionData[$maxPosition] = $productId;
                $maxPosition--;
            }
        }

        return $positionData;
    }
}
