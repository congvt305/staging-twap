<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 29/9/21
 * Time: 11:40 AM
 */
namespace Amore\GcrmDataExport\Override\Model\Export;

use Amore\GcrmDataExport\Helper\Data;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\CustomerImportExport\Model\Export\Customer as MainCustomer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Customer
 *
 * Overrided Customer class to add primary key in export file
 */
class Customer extends MainCustomer
{
    const XML_PATH_ACTIVE_EXTENSION = 'amore_gcrm/general/active';

    const HEADER_COMLUMNS = [
        'entity_id',
        'is_active',
        'increment_id',
        'email',
        '_website',
        '_store',
        'ba_code',
        'call_subscription_status',
        'confirmation',
        'created_at',
        'created_in',
        'disable_auto_group_change',
        'dm_subscription_status',
        'dob',
        'failures_num',
        'firstname',
        'first_failure',
        'gender',
        'group_id',
        'imported_from_pos',
        'integration_number',
        'lastname',
        'line_id',
        'line_message_agreement',
        'lock_expires',
        'middlename',
        'mobile_number',
        'partner_id',
        'password_hash',
        'prefix',
        'referrer_code',
        'reward_update_notification',
        'reward_warning_notification',
        'rp_token',
        'rp_token_created_at',
        'sales_office_code',
        'sales_organization_code',
        'status_code',
        'store_id',
        'suffix',
        'taxvat',
        'terms_and_services_policy',
        'updated_at',
        'website_id'
    ];

    /**
     * Columns to include in exported file
     *
     * @var array
     */
    private $includeColumns = [
        'entity_id',
        'is_active',
        'increment_id'
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
     * @var \Amore\GcrmDataExport\Model\Config\Config
     */
    private $gcrmConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param CollectionFactory $customerColFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Data $dataHelper
     * @param LoggerInterface $logger
     * @param \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        CollectionFactory $customerColFactory,
        DataPersistorInterface $dataPersistor,
        Data $dataHelper,
        LoggerInterface $logger,
        \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig,
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
            $data
        );
        $this->logger           = $logger;
        $this->dataHelper       = $dataHelper;
        $this->dataPersistor    = $dataPersistor;
        $this->gcrmConfig = $gcrmConfig;
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        return self::HEADER_COMLUMNS;
    }

    /**
     * Export given customer data
     *
     * @param CustomerModel $item
     * @return void
     */
    public function exportItem($item)
    {
        try {
            $row = $this->_addAttributeValuesToRow($item);
            if ($this->dataPersistor->get('gcrm_export_check')) {
                foreach ($this->includeColumns as $key => $columnName) {
                    $row[$columnName] = $item->getData($columnName);
                }
            }
            $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()] ?? '';
            $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()] ?? '';

            foreach ($row as $columnName => $value) {
                $row[$columnName] = $this->dataHelper->fixLineBreak($row[$columnName]);
            }

            $this->getWriter()->writeRow($row);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
    /**
     * Apply filter to collection and add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function _prepareEntityCollection(AbstractCollection $collection)
    {
        $this->filterEntityCollection($collection);
        $this->_addAttributesToCollection($collection);
        $storeEnable = [];
        foreach ($this->_storeManager->getStores() as $store) {
            if ($this->gcrmConfig->getConfigValue(self::XML_PATH_ACTIVE_EXTENSION, $store->getId())) {
                $storeEnable[] = $store->getId();
            }
        }
        $collection->addFieldToFilter('store_id', ['in' => $storeEnable]);
        return $collection;
    }
    /**
     * Fill row with attributes values
     *
     * @param \Magento\Framework\Model\AbstractModel $item export entity
     * @param array $row data row
     * @return array
     */
    protected function _addAttributeValuesToRow(\Magento\Framework\Model\AbstractModel $item, array $row = [])
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        // go through all valid attribute codes
        foreach ($validAttributeCodes as $attributeCode) {
            $attributeValue = $item->getData($attributeCode);

            if ($this->isMultiselect($attributeCode)) {
                $values = [];
                //Customize here
                if (!$attributeValue) {
                    continue;
                }
                $attributeValue = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $attributeValue);
                foreach ($attributeValue as $value) {
                    $values[] = $this->getAttributeValueById($attributeCode, $value);
                }
                $row[$attributeCode] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $values);
            } else {
                $row[$attributeCode] = $this->getAttributeValueById($attributeCode, $attributeValue);
            }
        }

        return $row;
    }
    /**
     * Checks that attribute is multiselect type by attribute code
     *
     * @param string $attributeCode An attribute code
     * @return bool Returns true if attribute is multiselect type
     */
    private function isMultiselect($attributeCode)
    {
        return isset($this->attributeTypes[$attributeCode])
            && $this->attributeTypes[$attributeCode] === 'multiselect';
    }

    /**
     * Returns attribute value by id
     *
     * @param string $attributeCode An attribute code
     * @param int|string $valueId
     * @return mixed
     */
    private function getAttributeValueById($attributeCode, $valueId)
    {
        if (isset($this->_attributeValues[$attributeCode])
            && isset($this->_attributeValues[$attributeCode][$valueId])
        ) {
            return $this->_attributeValues[$attributeCode][$valueId];
        }

        return $valueId;
    }
}
