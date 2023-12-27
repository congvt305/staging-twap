<?php

namespace CJ\CatalogProduct\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stores implements OptionSourceInterface
{

    protected $eavConfig;


    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }


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
