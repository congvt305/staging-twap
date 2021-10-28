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

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\CustomerImportExport\Model\Export\Customer as MainCustomer;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Customer
 *
 * Overrided Customer class to add primary key in export file
 */
class Customer extends MainCustomer
{
    /**#@+
     * Constants for export column.
     */
    const ENTITY_ID = 'entity_id';
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
     * @param CollectionFactory $customerColFactory
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
        CollectionFactory $customerColFactory,
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
            $data
        );
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        if ($this->dataPersistor->get('gcrm_export_check')) {
            array_splice(
                $this->_permanentAttributes,
                0,
                0,
                [self::ENTITY_ID]
            );
        }
        return array_merge($this->_permanentAttributes, $validAttributeCodes);
    }

    /**
     * Export given customer data
     *
     * @param CustomerModel $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        if ($this->dataPersistor->get('gcrm_export_check')) {
            $row[self::ENTITY_ID] = $item->getId();
        }
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()->writeRow($row);
    }
}
