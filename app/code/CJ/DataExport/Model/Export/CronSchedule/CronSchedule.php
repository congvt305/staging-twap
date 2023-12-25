<?php

namespace CJ\DataExport\Model\Export\CronSchedule;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

/**
 * Class CronSchedule
 */
class CronSchedule extends \Magento\ImportExport\Model\Export\AbstractEntity
    implements CronScheduleInterface
{
    /**
     * @var \CJ\DataExport\Model\Export\Adapter\CronScheduleCsv
     */
    protected $cronScheduleWriter;

    /**
     * @var \CJ\DataExport\Model\Export\CronSchedule\AttributeCollectionProvider
     */
    protected $attributeCollectionProvider;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Amore\GcrmDataExport\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    protected $cronScheduleColFactory;

    /**
     * @var \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory
     */
    protected $exportCollectionFactory;

    /**
     * @var \CJ\DataExport\Logger\Logger
     */
    protected $logger;
    /**
     * @var \CJ\DataExport\Model\Config\Config
     */
    protected $configHelper;

    /**
     * @var State
     */
    protected $state;
    protected $includeColumns = [
        'job_code' => 'job_code',
        'status' => 'status',
        'finished_at' => 'finished_at',
        'messages' => 'messages'
    ];
    protected $headColumnNames = [
        'job_code' => 'job_code',
        'status' => 'status',
        'finished_at' => 'finished_at',
        'messages' => 'messages'
    ];

    protected $importantCronJobs = [
        'order' => [
            'ecpay_einvoice_issue',
            'send_order_to_sap_cron',
            'update_product_ranking_cron',
            'eguana_pending_cancel_cron',
            'eguana_order_status_cron',
            'eguana_delivery_complete_status_cron',
            'eguana_ticket_close_cron',
            'eguana_rma_status_cron',
            'send_order_to_ninjavan_cron',
            'cj_sales_clean_payment_review_orders',
            'eguana_delivery_complete_cron_tw_blackcat',
            'eguana_gwlogistics_create_shipments'
        ],
        'customer' => [
            'amore_points_cron',
            'amore_resend_points_cron',
            'cj_poscstmno_syn',
            'cj_coupon_customer_create_customer_group'
        ],
        'product' => [
            'inventory_compensation_cron'
        ],
        'magento_important_cron' => [
            'backend_clean_cache',
            'indexer_reindex_all_invalid',
            'staging_apply_version',
            'consumers_runner',
            'sales_send_order_creditmemo_emails',
            'sales_send_order_emails',
            'sales_send_order_invoice_emails',
            'sales_send_order_shipment_emails',
        ],
        'other_cron' => [
            'cj_sms_cron_clean_history',
            'export_cronjob',
            'eguana_bizconnect_log_delete',
            'hoolah_extsettings_cronjob',
            'hoolah_orders_cronjob',
            'satp_search_terms_rank',
            'line_shopping_export_full_product',
            'line_shopping_export_partial_product',
            'line_shopping_export_full_category',
            'line_shopping_order_post_back',
            'line_shopping_fee_post_back'
        ]
    ];

    /**
     * @param \CJ\DataExport\Model\Export\Adapter\CronScheduleCsv $cronScheduleCsv
     * @param \CJ\DataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Amore\GcrmDataExport\Helper\Data $dataHelper
     * @param \CJ\DataExport\Logger\Logger $logger
     * @param \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronScheduleColFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param State $state
     * @param array $data
     */
    public function __construct(
        \CJ\DataExport\Model\Export\Adapter\CronScheduleCsv $cronScheduleCsv,
        \CJ\DataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amore\GcrmDataExport\Helper\Data $dataHelper,
        \CJ\DataExport\Logger\Logger $logger,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronScheduleColFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        State $state,
        array $data = []
    ) {
        $this->cronScheduleWriter = $cronScheduleCsv;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->messageManager = $messageManager;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->exportCollectionFactory = $exportCollectionFactory;
        $this->cronScheduleColFactory = $cronScheduleColFactory;
        $this->state = $state;

        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function export()
    {
        /** @var \CJ\DataExport\Model\Export\Adapter\OrderCsv $writer */
        $writer = $this->getWriter();

        $cronScheduleData = $this->getCronScheduleData();

        if ($cronScheduleData == null) {
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return false;
        }

        $index = 0;
        $headersData = [];

        foreach ($cronScheduleData as $schedules) {
            foreach ($schedules as $schedule) {
                if ($index == 0) {
                    $headersData[] = 'category';
                    foreach (array_keys($schedule) as $key) {
                        $headersData[] = $key;
                        $index += 1;
                    }
                    $writer->setHeaderCols($headersData);
                }

                $category = $this->getCategoryForJob($schedule[CronScheduleInterface::JOB_CODE]);
                array_unshift($schedule, $category);
                $writer->writeRow($schedule);
            }
        }
        return $writer->getContents();
    }

    /**
     * @param $jobCode
     * @return string|void
     */
    private function getCategoryForJob($jobCode)
    {
        $jobs = $this->importantCronJobs;
        $found = false;
        $category = '';
        foreach ($jobs as $cat => $jobCodes) {
            foreach ($jobCodes as $code) {
                if ($jobCode == $code) {
                    $found = true;
                    $category = $cat;
                    break;
                }
            }
        }

        if ($found) {
            return $category;
        }
    }

    /**
     *{@inheritDoc}
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     *{@inheritDoc}
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_TYPE;
    }

    /**
     *{@inheritDoc}
     */
    protected function _getHeaderColumns()
    {
        $header = [];
        $header[] = 'category';
        foreach ($this->headColumnNames as $englishColumn) {
            $header[] = $englishColumn;
        }
        return $header;
    }

    /**
     *{@inheritDoc}
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }


    /**
     * @return array
     */
    public function getCronScheduleData()
    {
        $itemRow = [];
        $collection = $this->_getEntityCollection();

        $cnt = 0;
        foreach ($collection as $item) {

            $itemData = $this->dataHelper->fixSingleRowData($item->getData());
            $itemRow[$item->getScheduleId()][$cnt] = $itemData;
            $cnt++;
        }
        return $itemRow;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected function _getEntityCollection()
    {
        try {
            $customExportData = $this->exportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => self::ENTITY_TYPE])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');

            if ($this->state->getAreaCode() === Area::AREA_ADMINHTML && $customExportData->getData('export_from_date')) {
                $exportDate = $customExportData->getData('export_from_date');
            }

            if ($exportDate == "NULL") {
                /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collection */
                $collection = $this->cronScheduleColFactory->create();
                foreach ($this->includeColumns as $alias => $fieldName) {
                    $collection->addFieldToSelect($fieldName, $alias);
                }
            } else {
                /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
                $collection = $this->cronScheduleColFactory->create();
                $connection = $collection->getConnection();
                foreach ($this->includeColumns as $alias => $fieldName) {
                    $collection->addFieldToSelect($fieldName, $alias);
                }
                $collection->addFieldToFilter('main_table.finished_at', ['gt' => $exportDate]);

            }

            $allowJobCodes = [];
            foreach ($this->importantCronJobs as $category => $jobs) {
                foreach ($jobs as $job) {
                    $allowJobCodes[] = $job;
                }
            }
            $collection->addFieldToFilter('main_table.job_code', ['IN' => $allowJobCodes]);
            $collection->addFieldToFilter('main_table.status', ['IN' => ['success', 'error']]);
        } catch (\Exception $e) {
            $this->logger->log('info', $e->getMessage());
        }

        return $collection;
    }

    /**
     * Set Order Writer for CSV File
     *
     * @param \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter $cronScheduleWriter
     * @return $this
     */
    public function setWriter(AbstractAdapter $cronScheduleWriter)
    {
        $this->cronScheduleWriter = $cronScheduleWriter;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getWriter()
    {
        return $this->cronScheduleWriter;
    }
}
