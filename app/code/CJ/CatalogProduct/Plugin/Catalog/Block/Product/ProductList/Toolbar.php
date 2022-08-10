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
    
    public function aroundSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, \Closure $proceed, $collection)
    {
        $store = $this->storeManager->getStore();
        $result = $proceed($collection);
        if ($store->getCode() == 'my_sulwhasoo'){
            $currentOrder = $subject->getCurrentOrder();
            if($currentOrder) {
                if($currentOrder == 'high_to_low') {
                    $subject->getCollection()->getSelect()->order('price_index.price desc');
                } elseif ($currentOrder == 'low_to_high') {
                    $subject->getCollection()->getSelect()->order('price_index.price asc');
                }elseif ($this->data->getEnableFilterOnSale($store->getId()) && $subject->getRequest()->getParam('on_sale')){
                    $subject->getCollection()->getSelect()->where('price_index.final_price < price_index.price');
                }
            }
        }

        return $result;
    }
}
