<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CJ\SFLocker\CustomerData;

use Eguana\Directory\Model\ResourceModel\City\CollectionFactory;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cart source
 */
class DirectoryData implements SectionSourceInterface
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var CollectionFactory
     */
    private $_cityCollectionFactory;

    /**
     * @param Data $directoryHelper
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        Data $directoryHelper,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->directoryHelper = $directoryHelper;
        $this->_cityCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();

        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            $output[$code]['name'] = $data->getName();
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $cityCollection = $this->_cityCollectionFactory->create();
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                    if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
                        $output[$code]['regions'][$key]['city'] = $cityCollection->addRegionIdFilter($key)->getData();
                    }
                }
            }
        }
        return $output;
    }

}
