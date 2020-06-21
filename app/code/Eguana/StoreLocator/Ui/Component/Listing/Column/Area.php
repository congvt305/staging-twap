<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: shahroz
 * Date: 28/1/20
 * Time: 2:42 PM
 */
namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Magento\Directory\Model\CountryFactory;
//use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Area SourceModel class
 *
 * Class Area
 *  Eguana\StoreLocator\Ui\Component\Listing\Column
 */
class Area implements OptionSourceInterface
{
    protected $_regionCollection;

    protected $_options;

    private $_countryFactory;

    /**
     * Area constructor.
     * @param CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        CollectionFactory $regionCollectionFactory,
        CountryFactory $countryFactory
    ) {
        $this->_regionCollection = $regionCollectionFactory->create();
        $this->_countryFactory = $countryFactory;
    }

    /**
     * Source model for area
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options == null) {
            $optionValue = $this->getOptionValue();
            foreach ($optionValue as $value => $label) {
                $this->_options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        }
        return $this->_options;
    }

    /**
     * helper function for fetching countries
     * @return array
     */
    private function getOptionValue()
    {
        $usRegionCollection = $this->getAllRegionCollection();
        $optionValue = [];
        foreach ($usRegionCollection as $regionItem) {
            $optionValue[$regionItem->getCountryId()] =
                $this->_countryFactory->create()->loadByCode($regionItem->getCountryId())->getName();
        }
        return $optionValue;
    }

    /**
     * Helper function for fetching us regions
     * @return Collection
     */
    private function getAllRegionCollection()
    {
        $usRegionCollection = $this->_regionCollection->loadByStore();
        $usRegionCollection->addFieldToSelect('*');
        return $usRegionCollection;
    }
}
