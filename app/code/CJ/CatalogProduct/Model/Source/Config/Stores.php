<?php

namespace CJ\CatalogProduct\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Stores
 * @package CJ\CatalogProduct\Model\Source\Config
 */
class Stores implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Stores constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $storeManagerDataList = $this->_storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $value) {
            $options[] = ['value' => $value->getId(), 'label' => $value->getName()];
        }
        return $options;
    }
}
