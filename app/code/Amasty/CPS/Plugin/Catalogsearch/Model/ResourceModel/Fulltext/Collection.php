<?php

namespace Amasty\CPS\Plugin\Catalogsearch\Model\ResourceModel\Fulltext;

use Amasty\CPS\Api\Data\BrandProductInterface;
use \Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Collection
{
    public const POSITION_COLUMN_NAME = 'cat_index_position';

    public const COLUMN_NAME_INDEX = 2;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Content
     */
    private $contentHelper;

    /**
     * @var bool
     */
    private $isPositionIndexJoinApplied = false;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        \Amasty\ShopbyBrand\Helper\Content $contentHelper,
        \Amasty\Base\Model\MagentoVersion $magentoVersion
    ) {
        $this->contentHelper = $contentHelper;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function afterAddCategoryFilter($collection)
    {
        if ($this->contentHelper->getCurrentBranding()) {
            $this->addPositionIndexJoin($collection);
        }

        return $collection;
    }

    /**
     * @param $collection
     * @return $this
     */
    private function addPositionIndexJoin($collection)
    {
        if (!$this->isPositionIndexJoinApplied) {
            /**
             * @var \Magento\Framework\DB\Select $select
             */
            $select = $collection->getSelect();
            $brand = $this->contentHelper->getCurrentBranding();
            $columns = $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
            foreach ($columns as $index => $column) {
                if (isset($column[self::COLUMN_NAME_INDEX])
                    && $column[self::COLUMN_NAME_INDEX] == self::POSITION_COLUMN_NAME
                ) {
                    unset($columns[$index]);
                    break;
                }
            }
            $select->setPart(\Magento\Framework\DB\Select::COLUMNS, $columns);
            $table = BrandProductInterface::MAIN_TABLE;

            if (version_compare($this->magentoVersion->get(), '2.3.2', '>=')) {
                $positionExpr = new \Zend_Db_Expr("IFNULL(brand_product_index.position, 0)");
            } else {
                $positionExpr = new \Zend_Db_Expr("IFNULL(brand_product_index.position, cat_index.position)");
            }

            $storeId = (int)$brand->getData(BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING)
                ? $collection->getStoreId()
                : 0;
            $select->joinInner(
                ['brand_product_index' => $collection->getResource()->getTable($table)],
                'brand_product_index.product_id = e.entity_id'
                . ' AND brand_product_index.ambrand_id = ' . $brand->getValue()
                . ' AND brand_product_index.store_id = ' . $storeId,
                ['cat_index_position' => $positionExpr]
            );
            $this->isPositionIndexJoinApplied = true;
        }

        return $this;
    }

    /**
     * @param $collection
     * @param callable $proceed
     * @param $attribute
     * @param $dir
     * @return mixed
     */
    public function aroundAddAttributeToSort(
        $collection,
        callable $proceed,
        $attribute,
        $dir = AbstractCollection::SORT_ORDER_ASC
    ) {
        if ($this->contentHelper->getCurrentBranding() && $attribute == 'position') {
            $this->addPositionIndexJoin($collection);
            $collection->getSelect()->order("cat_index_position {$dir}");
        }

        return $proceed($attribute, $dir);
    }
}
