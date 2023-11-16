<?php
namespace CJ\CatalogProduct\Plugin\Catalog\Model;

class Config
{
    /**
     * @var \CJ\CatalogProduct\Helper\Data
     */
    protected $data;
    /**
     * @param \CJ\CatalogProduct\Helper\Data $data
     */
    public function __construct(\CJ\CatalogProduct\Helper\Data $data)
    {
        $this->data = $data;
    }

    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $storeid = $catalogConfig->getStoreId();

        if ($storeid && $this->data->getEnableSortBestSellers($storeid)){
            if (isset($options['ranking'])){
                $options['ranking'] = __("Best Sellers");
            }
        }

        return $options;
    }
}
