<?php

namespace Eguana\Directory\Model\ResourceModel\Ward;


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
        $this->_init(\Eguana\Directory\Model\Ward::class, \Eguana\Directory\Model\ResourceModel\Ward::class);
    }

    public function toOptionArray()
    {
        $options = [];
        $propertyMap = [
            'value' => 'ward_id',
            'code' => 'code',
            'title' => 'default_name',
            'city_id' => 'city_id',
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
                ['title' => '', 'value' => '0000', 'label' => __('Please select a ward')]
            );
        }
        return $options;
    }
}
