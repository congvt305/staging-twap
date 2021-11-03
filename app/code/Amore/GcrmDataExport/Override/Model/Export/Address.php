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
     * @var DataPersistorInterface
     */
    private $dataPersistor;

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
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        if ($this->dataPersistor->get('gcrm_export_check')) {
            $this->_permanentAttributes = [self::ENTITY_ID, self::PARENT_ID, self::COLUMN_WEBSITE, self::COLUMN_EMAIL];
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
        $row[$entityColumn] = $item['entity_id'];

        $row[self::PARENT_ID] = $item->getParentId();
        $row[self::COLUMN_EMAIL] = $customer['email'];
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$customer['website_id']];
        $row[self::COLUMN_REGION_ID] = $item->getRegionId();

        $this->getWriter()->writeRow($row);
    }
}
