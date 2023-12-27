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

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, \Magento\Framework\Data\Collection $collection)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId && $this->data->getEnableFilterOnSale($storeId) && $subject->getRequest()->getParam('on_sale')) {
            $collection->getSelect()->where('price_index.final_price < price_index.price');
        }
    }
}
