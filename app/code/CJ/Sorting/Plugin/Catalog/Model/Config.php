<?php

namespace CJ\Sorting\Plugin\Catalog\Model;
use \Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package CJ\Sorting\Plugin\Catalog\Model
 */
class Config
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Toolbar constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * @param CatalogConfig $catalogConfig
     * @param $results
     * @return mixed
     */
    public function afterGetAttributeUsedForSortByArray(CatalogConfig $catalogConfig, $results)
    {
        if ($this->_storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $results['created_at'] = __('Created At');
        }

        return $results;
    }
}
