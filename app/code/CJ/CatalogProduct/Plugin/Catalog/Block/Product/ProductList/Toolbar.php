<?php

namespace CJ\CatalogProduct\Plugin\Catalog\Block\Product\ProductList;

use CJ\CatalogProduct\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Toolbar
{
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Data $data, StoreManagerInterface $storeManager)
    {
        $this->data = $data;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetCurrentDirection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $result)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId && $this->data->getEnableSortBestSellers($storeId)) {
            if ($subject->getCurrentOrder() == Data::RANKING_ATTRIBUTE_CODE) {
                $result = 'asc';
            }
        }
        return $result;
    }
}
