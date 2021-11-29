<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 29/9/21
 * Time: 1:07 PM
 */
namespace Amore\GcrmDataExport\Override\Model\Export;

use Amore\GcrmDataExport\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\CustomerImportExport\Model\Export\Address as MainAddress;
use Magento\CustomerImportExport\Model\Export\CustomerFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Address
 *
 * Overrided Address class to add primary key in export file
 */
class Address extends MainAddress
{
    /**#@+
     * Constants for export column.
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    /**#@-*/

    /**
     * Columns to include in exported file
     *
     * @var array
     */
    private $includeColumns = [
        'entity_id',
        'parent_id',
        'is_active',
        'increment_id',
        'created_at',
        'updated_at'
    ];

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param CustomerCollectionFactory $customerColFactory
     * @param CustomerFactory $eavCustomerFactory
     * @param AddressCollectionFactory $addressColFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Data $dataHelper
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        CustomerCollectionFactory $customerColFactory,
        CustomerFactory $eavCustomerFactory,
        AddressCollectionFactory $addressColFactory,
        DataPersistorInterface $dataPersistor,
        Data $dataHelper,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $customerColFactory,
            $eavCustomerFactory,
            $addressColFactory,
            $data
        );
        $this->logger           = $logger;
        $this->dataHelper       = $dataHelper;
        $this->dataPersistor    = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        if ($this->dataPersistor->get('gcrm_export_check')) {
            $this->_permanentAttributes = array_merge(
                $this->includeColumns,
                [self::COLUMN_WEBSITE, self::COLUMN_EMAIL]
            );
        }
        return array_merge(
            $this->_permanentAttributes,
            $this->_getExportAttributeCodes(),
            array_keys(self::$_defaultAddressAttributeMapping)
        );
    }

    /**
     * Export given customer address data plus related customer data (required for import)
     *
     * @param AddressModel $item
     * @return void
     */
    public function exportItem($item)
    {
        try {
            $row = $this->_addAttributeValuesToRow($item);

            /** @var $customer Customer */
            $customer = $this->_customers[$item->getParentId()];

            // Fill row with default address attributes values
            foreach (self::$_defaultAddressAttributeMapping as $columnName => $attributeCode) {
                if (!empty($customer[$attributeCode]) && $customer[$attributeCode] == $item->getId()) {
                    $row[$columnName] = 1;
                }
            }

            // Unique key
            $entityColumn = $this->dataPersistor->get('gcrm_export_check') ? self::ENTITY_ID : self::COLUMN_ADDRESS_ID;
            $row[$entityColumn] = $item->getId() ?? '';

            if ($this->dataPersistor->get('gcrm_export_check')) {
                $row[self::PARENT_ID] = $item->getParentId();
                foreach ($this->includeColumns as $key => $columnName) {
                    if ($columnName != self::ENTITY_ID && $columnName != self::PARENT_ID) {
                        $row[$columnName] = $item->getData($columnName);
                    }
                }
            }

            $row[self::COLUMN_EMAIL] = $customer['email'] ?? '';
            $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$customer['website_id']] ?? '';
            $row[self::COLUMN_REGION_ID] = $item->getRegionId() ?? '';

            foreach ($row as $columnName => $value) {
                $row[$columnName] = $this->dataHelper->fixLineBreak($row[$columnName]);
            }

            $this->getWriter()->writeRow($row);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
