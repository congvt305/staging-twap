<?php
namespace CJ\CatalogProduct\Plugin\Catalog\Model;

class Config
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \CJ\CatalogProduct\Helper\Data
     */
    protected $data;
    /**
     * @param \CJ\CatalogProduct\Helper\Data $data
     */
    public function __construct(\CJ\CatalogProduct\Helper\Data $data, \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->data = $data;
        $this->storeManager = $storeManager;
    }

    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $storeid = $catalogConfig->getStoreId();

        if ($storeid && $this->data->getEnableSortBestSellers($storeid)){
            if (isset($options['ranking'])){
                $options['ranking'] = __("Best Sellers");
            }
        }
        $storeCode = $this->storeManager->getStore()->getCode();
        if ($storeCode == 'my_sulwhasoo'){
            $options['low_to_high'] = __('Price - Low To High');
            $options['high_to_low'] = __('Price - High To Low');
        }

        return $options;
    }
}
