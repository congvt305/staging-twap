<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/3/20
 * Time: 1:06 PM
 */

namespace Eguana\Directory\Model\ResourceModel\City;


use Magento\Store\Model\ScopeInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null
     */
    private $resource;
    /**
     * @var string
     */
    private $regionTable;
    /**
     * @var \Magento\Directory\Model\AllowedCountries
     */
    private $allowedCountries;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Directory\Model\AllowedCountries $allowedCountries,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->allowedCountries = $allowedCountries;
        $this->resource = $resource;

    }

    protected function _construct()
    {
        $this->_init(\Eguana\Directory\Model\City::class, \Eguana\Directory\Model\ResourceModel\City::class);
        $this->regionTable = $this->getTable('directory_country_region');
        $this->addOrder('region_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['region' => 'directory_country_region'],
            'main_table.region_id = region.region_id', // (main_table.order_id = soa.parent_id)
            [
                'country_id',
            ]
        );
        return $this;
    }

    public function addAllowedCountriesFilter($store = null)
    {
        $allowedCountries = $this->allowedCountries->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store);

        if (!empty($allowedCountries)) {
//            $this->addFieldToFilter('main_table.country_id', ['in' => $allowedCountries]);
            $this->addFieldToFilter('country_id', ['in' => $allowedCountries]);
        }
        return $this;
    }

//    public function addCountryFilter($countryId)
//    {
//        if (!empty($countryId)) {
//            if (is_array($countryId)) {
//                $this->addFieldToFilter('country_id', ['in' => $countryId]);
//            } else {
//                $this->addFieldToFilter('country_id', $countryId);
//            }
//        }
//        return $this;
//    }

//    public function addRegionCodeFilter($regionCode)
//    {
//        if (!empty($regionCode)) {
//            if (is_array($regionCode)) {
//                $this->addFieldToFilter('main_table.region_code', ['in' => $regionCode]);
//            } else {
//                $this->addFieldToFilter('main_table.region_code', $regionCode);
//            }
//        }
//        return $this;
//    }
//    public function addRegionIdFilter($regionId)
//    {
//        if (!empty($regionId)) {
//            if (is_array($regionId)) {
//                $this->addFieldToFilter('main_table.region_id', ['in' => $regionId]);
//            } else {
//                $this->addFieldToFilter('main_table.region_id', $regionId);
//            }
//        }
//        return $this;
//    }

    public function toOptionArray()
    {
        $options = [];
        $propertyMap = [
            'value' => 'city_id',
            'code' => 'code',
            'title' => 'default_name',
            'region_id' => 'region_id',
        ];

        foreach ($this as $item) {
            $option = [];
            foreach ($propertyMap as $code => $field) {
                $option[$code] = $item->getData($field);
            }
            $option['label'] = $item->getName();
            $options[] = $option;
        }

        if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a city or district.')]
            );
        }
        return $options;
    }
}
