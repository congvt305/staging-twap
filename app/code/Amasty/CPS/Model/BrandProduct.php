<?php

namespace Amasty\CPS\Model;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Amasty\CPS\Model\ResourceModel\BrandProduct as BrandProductResource;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\Framework\DataObjectFactory as ObjectFactory;

class BrandProduct implements BrandProductInterface
{
    /**
     * @var BrandProductResource
     */
    private $resource;

    /**
     * @var Product\AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $settingRepository;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $config;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(
        BrandProductResource $brandProductResource,
        \Amasty\CPS\Model\Product\AdminhtmlDataProvider $dataProvider,
        \Amasty\ShopbyBrand\Helper\Data $config,
        OptionSettingRepositoryInterface $settingRepository,
        ObjectFactory $objectFactory
    ) {
        $this->resource = $brandProductResource;
        $this->dataProvider = $dataProvider;
        $this->settingRepository = $settingRepository;
        $this->config = $config;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @param int $brandId
     * @param array $productPositionData
     * @param array $pinnedProductIds
     * @return BrandProductInterface
     */
    public function updateBrandProductPositionDataByBrand($brandId, $productPositionData = [], $pinnedProductIds = [])
    {
        foreach ($productPositionData as $storeId => $positionData) {
            $this->resource->updateProductPositionsByBrand($brandId, $storeId, $productPositionData, $pinnedProductIds);
        }

        return $this;
    }

    /**
     * @return BrandProductInterface
     */
    public function clearBrandsPositionData()
    {
        $this->resource->truncateMainTable();
        return $this;
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @return array
     */
    public function getBrandProductPositionDataByStore($brandId, $storeId)
    {
        return $this->resource->getProductPositionData($brandId, $storeId);
    }

    /**=
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sortProductsByOrder()
    {
        $pinnedProductIds = array_flip($this->dataProvider->getProductPositionData());
        $collection = $this->dataProvider->getProductCollection();
        $this->dataProvider->setCollectionOrder($collection);
        $productPositionData = array_column($collection->getData(), 'entity_id');

        $preparedData = $this->prepareProductData($productPositionData, $pinnedProductIds);

        $productIds = $preparedData->getData('productPositionData');
        ksort($productIds);
        $this->dataProvider->setProductIds(array_flip($productIds));
    }

    /**
     * @param int $brandId
     * @param int $storeId
     * @param array $pinnedProductIds
     */
    public function pinProduct($brandId, $storeId, $pinnedProductIds)
    {
        $productPositionData = $this->dataProvider->getProductIds();
        $preparedData = $this->prepareProductData(
            array_flip($productPositionData),
            array_flip($pinnedProductIds)
        );

        $this->resource->updateProductPositionsByBrand(
            $brandId,
            $storeId,
            array_flip($preparedData->getData('productPositionData')),
            $preparedData->getData('pinnedProductIds')
        );
    }

    /**
     * @param array $productPositionData
     * @param array $pinnedProductIds
     * @return \Magento\Framework\DataObject
     */
    protected function prepareProductData($productPositionData = [], $pinnedProductIds = [])
    {
        $productPositionData = array_diff($productPositionData, $pinnedProductIds);
        $productPositionData = $this->sortProducts($productPositionData, $pinnedProductIds);

        return $this->objectFactory->create()->setData(
            [
                'productPositionData' => $productPositionData,
                'pinnedProductIds' => $pinnedProductIds,
            ]
        );
    }

    /**
     * @param array $productPositionData
     * @param array $pinnedProductIds
     * @return array
     */
    protected function sortProducts(array $productPositionData, array $pinnedProductIds)
    {
        for ($i = 0; $productPositionData; $i++) {
            if (!isset($pinnedProductIds[$i])) {
                $pinnedProductIds[$i] = array_shift($productPositionData);
            }
        }

        return $pinnedProductIds;
    }
}
