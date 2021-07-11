<?php
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 05. 25. 2021
 */

namespace Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate;

class LocationDirectory extends \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\LocationDirectory
{
    /**
     * @var array
     */
    private $cities;
    /**
     * @var \Eguana\Directory\Model\ResourceModel\City\CollectionFactory
     */
    private $cityCollectionFactory;

    public function __construct(
        \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    ) {
        parent::__construct($countryCollectionFactory, $regionCollectionFactory);
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    public function hasCityName($regionIds, $cityName)
    {
        $regionId = reset($regionIds);
        $this->loadCities();
        return isset($this->cities[$regionId][$cityName]);
    }

    private function loadCities()
    {
        if ($this->cities !== null) {
            return $this;
        }
        $this->cities = [];

        /** @var $collection \Eguana\Directory\Model\ResourceModel\City\Collection */
        $collection = $this->cityCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->cities[$row['region_id']][$row['default_name']] = (int)$row['city_id'];
        }
        return $this;
    }

    public function getCityId($regionIds, $cityName)
    {
        $regionId = reset($regionIds);
        $this->loadCities();
        return $this->cities[$regionId][$cityName];
    }
}

