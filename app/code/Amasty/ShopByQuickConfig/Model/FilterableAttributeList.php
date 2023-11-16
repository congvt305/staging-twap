<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Model;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList as CatalogAttributeList;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Using the same collection as catalog category do.
 *   Collection should have same fields, joins, filters
 *   otherwise MYSQL can cache different result
 *   witch may vary from frontend if position (main sorting field for catalog attributes) are the same.
 *   Because Mysql sorts randomly if order field values are the same.
 */
class FilterableAttributeList extends CatalogAttributeList
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($collectionFactory, $storeManager);
        $this->scopeConfig = $scopeConfig;
    }

    protected function _prepareAttributeCollection($collection)
    {
        $collection->setItemObjectClass(FilterData::class);
        parent::_prepareAttributeCollection($collection);

        $filterCode = sprintf('CONCAT(\'%s\',  %s)', FilterSetting::ATTR_PREFIX, FilterData::ATTRIBUTE_CODE);

        // without reset columns, otherwise sorting result may be differ with frontend
        $select = $collection->getSelect()
            ->columns([FilterData::ATTRIBUTE_ID, FilterData::ATTRIBUTE_CODE, FilterData::LABEL => 'frontend_label'])
            ->columns([FilterData::FILTER_CODE => $filterCode])
            ->order(['position ASC']);

        if ($this->isCategoryFilterable()) {
            $select->orWhere('attribute_code = ?', FiltersProvider::CATEGORY_ATTRIBUTE_CODE);
        }

        return $collection;
    }

    private function isCategoryFilterable(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amshopby/' . FiltersProvider::CATEGORY_FILTER_CODE . '_filter/enabled',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
