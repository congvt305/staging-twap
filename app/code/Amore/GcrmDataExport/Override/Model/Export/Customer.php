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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
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
                $this->includeColumns
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
        try {
            $row = $this->_addAttributeValuesToRow($item);
            if ($this->dataPersistor->get('gcrm_export_check')) {
                foreach ($this->includeColumns as $key => $columnName) {
                    $row[$columnName] = $item->getData($columnName);
                }
            }
            $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
            $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

            foreach ($row as $columnName => $value) {
                $row[$columnName] = $this->dataHelper->fixLineBreak($row[$columnName]);
            }

            $this->getWriter()->writeRow($row);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
