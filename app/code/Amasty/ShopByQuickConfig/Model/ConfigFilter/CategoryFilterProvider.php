<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Model\ConfigFilter;

use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FilterDataFactory;
use Amasty\ShopByQuickConfig\Model\FiltersProvider;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CategoryFilterProvider
{
    /**
     * @var FilterDataFactory
     */
    private $filterDataFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        FilterDataFactory $filterDataFactory,
        ScopeConfigInterface $scopeConfig,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->filterDataFactory = $filterDataFactory;
        $this->scopeConfig = $scopeConfig;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return FilterData
     */
    public function get(): FilterData
    {
        $model = $this->filterDataFactory->create();

        $attribute = $this->attributeRepository->get(FiltersProvider::CATEGORY_ATTRIBUTE_CODE);

        $model->addData($attribute->getData());
        $model->setIsEnabled($this->getIsEnabled());
        $model->setFilterCode(FiltersProvider::CATEGORY_ATTRIBUTE_CODE);
        $model->setLabel($attribute->getFrontendLabel());

        return $model;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return (bool)(int) $this->scopeConfig->getValue(
            'amshopby/category_filter/enabled',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
