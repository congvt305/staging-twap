<?php

namespace Amasty\CPS\Plugin\ElasticSearch\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionSettingCollectionFactory;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Amasty\CPS\Api\Data\BrandProductInterface;

class BrandProductProvider
{
    /**
     * @var  Repository
     */
    protected $repository;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var OptionSettingCollectionFactory
     */
    private $optionSettingCollectionFactory;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSettingFactory
     */
    private $optionSettingFactory;

    /**
     * @var \Amasty\CPS\Model\ResourceModel\BrandProduct
     */
    private $brandProductResource;

    /**
     * @var array
     */
    private $settingByValue = [];

    public function __construct(
        Repository $repository,
        \Amasty\ShopbyBase\Model\OptionSettingFactory $optionSettingFactory,
        OptionSettingCollectionFactory $optionSettingCollectionFactory,
        DataHelper $helper,
        \Amasty\CPS\Model\ResourceModel\BrandProduct $brandProductResource
    ) {
        $this->repository = $repository;
        $this->helper = $helper;
        $this->optionSettingCollectionFactory = $optionSettingCollectionFactory;
        $this->optionSettingFactory = $optionSettingFactory;
        $this->brandProductResource = $brandProductResource;
    }

    /**
     * @param array $productIds
     * @param $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandProductsData(array $productIds, $storeId)
    {
        $attributeCode = $this->helper->getBrandAttributeCode();
        if (!$attributeCode) {
            return [];
        }

        $options = $this->repository->get($attributeCode)->getOptions();
        array_shift($options);
        $positionData = [];
        foreach ($options as $option) {
            $setting = $this->getBrandOptionSettingByValue($option->getValue(), $storeId);
            $useDefault = $setting->getData(BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING);
            $brandPositionData = $this->brandProductResource->getBrandIdsByProductIds(
                $productIds,
                $useDefault ? $storeId : \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                $option->getValue()
            );
            // phpcs:ignore
            $positionData = array_replace_recursive($positionData, $brandPositionData);
        }

        return $positionData;
    }

    /**
     * @param int $value
     * @param int $storeId
     * @return OptionSettingInterface
     */
    private function getBrandOptionSettingByValue($value, $storeId)
    {
        if (empty($this->settingByValue)) {
            $stores = [0,  $storeId];
            $collection = $this->optionSettingCollectionFactory->create()
                ->addFieldToFilter('store_id', $stores)
                ->addFieldToFilter('filter_code', $this->helper->getBrandAttributeCode())
                ->addOrder('store_id', 'ASC'); //current store values will rewrite defaults
            foreach ($collection as $item) {
                $this->settingByValue[$item->getValue()] = $item;
            }
        }

        return isset($this->settingByValue[$value])
            ? $this->settingByValue[$value] : $this->optionSettingFactory->create() ;
    }
}
