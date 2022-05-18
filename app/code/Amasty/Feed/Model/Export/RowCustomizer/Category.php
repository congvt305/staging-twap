<?php

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Category\Repository;
use Amasty\Feed\Model\Category\ResourceModel\CollectionFactory;
use Amasty\Feed\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Category implements RowCustomizerInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Product
     */
    protected $export;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $mappingCategories;

    /**
     * @var array
     */
    protected $mappingData;

    /**
     * @var array
     */
    protected $rowCategories;

    /**
     * @var array
     */
    protected $categoriesPath;

    /**
     * @var array
     */
    protected $categoriesLast;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Repository
     */
    private $categoryRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Product $export,
        CollectionFactory $categoryCollectionFactory,
        Repository $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->export = $export;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE)
            || $this->export->hasAttributes(Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)
        ) {
            $_skippedCategories = [];

            $this->mappingCategories = array_merge(
                $this->export->getAttributesByType(Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE),
                $this->export->getAttributesByType(Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)
            );

            /** @var \Amasty\Feed\Model\Category\ResourceModel\Collection $categoryCollection */
            $categoryCollection = $this->categoryCollectionFactory->create()
                ->addOrder('name')
                ->addFieldToFilter('code', ['in' => $this->mappingCategories]);
            foreach ($this->categoryRepository->getItemsWithDeps($categoryCollection) as $category) {
                $this->mappingData[$category->getCode()] = [];

                foreach ($category->getMapping() as $mapping) {
                    if ($mapping->getData('skip')) {
                        $_skippedCategories[] = $mapping->getCategoryId();
                    }
                    $this->mappingData[$category->getCode()][$mapping->getCategoryId()] = $mapping->getVariable();
                }
            }

            $rowsCategoriesNew = [];
            $multiRowData = $this->export->getMultiRowData();
            $rowsCategories = $multiRowData['rowCategories'];

            foreach ($rowsCategories as $id => $rowCategories) {
                $rowCategories = array_diff($rowCategories, $_skippedCategories);

                if (!empty($rowCategories)) {
                    $rowsCategoriesNew[$id] = $rowCategories;
                }
            }
            $this->rowCategories = $rowsCategoriesNew;

            $this->categoriesPath = $this->export->getCategoriesPath();
            $this->categoriesLast = $this->export->getCategoriesLast();
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];
        $customData[Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE] = [];
        $customData[Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE] = [];

        if (is_array($this->mappingCategories)) {
            foreach ($this->mappingCategories as $code) {
                if (isset($this->rowCategories[$productId])) {
                    $categories = $this->rowCategories[$productId];
                    $lastCategoryId = $this->getLastCategoryId($categories);

                    if (isset($this->categoriesLast[$lastCategoryId]) && is_array($this->mappingCategories)) {
                        $lastCategoryVar = $this->categoriesLast[$lastCategoryId];

                        if (isset($this->mappingData[$code][$lastCategoryId])) {
                            $customData[Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE][$code] =
                                $this->mappingData[$code][$lastCategoryId];
                        } else {
                            $customData[Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE][$code] = $lastCategoryVar;
                        }
                    }

                    $customData[Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE][$code] = implode(
                        ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                        $this->getCategoriesPath($categories, $code)
                    );
                }
            }
        }

        return $dataRow;
    }

    /**
     * @param array $categories
     *
     * @return int|null
     */
    private function getLastCategoryId($categories)
    {
        while (count($categories) > 0) {
            $endCategoryId = array_pop($categories);

            foreach ($this->mappingCategories as $code) {
                if (isset($this->mappingData[$code][$endCategoryId])) {
                    return $endCategoryId;
                }
            }
        }

        return null;
    }

    /**
     * @param array $categories
     * @param string $code
     *
     * @return array
     */
    private function getCategoriesPath($categories, $code)
    {
        $categoriesPath = [];

        foreach ($categories as $categoryId) {
            if (isset($this->categoriesPath[$categoryId])) {
                $path = $this->categoriesPath[$categoryId];

                foreach ($path as $id => $var) {
                    if (isset($this->mappingData[$code][$id])) {
                        $path[$id] = $this->mappingData[$code][$id];
                    } else {
                        $path[$id] = $var;
                    }
                }

                $categoriesPath[] = implode('/', $path);
            }
        }

        return $categoriesPath;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
