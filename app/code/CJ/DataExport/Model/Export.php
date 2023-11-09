<?php

namespace CJ\DataExport\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use CJ\DataExport\Model\Export\CronSchedule\CronScheduleInterface;

/**
 * Class Export
 */
class Export extends \Amore\GcrmDataExport\Model\Export
{
    /**
     * @var \CJ\DataExport\Model\Export\Adapter\OrderCsv
     */
    protected $cjOrderWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RedemptionCsv
     */
    protected $cjRedemptionWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RedemptionPosCsv
     */
    protected $cjRedemptionPosWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RmaCsv
     */
    protected $cjRmaWriter;

    /**
     * @var \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $fileFormats;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\CronScheduleCsv
     */
    protected $cjCronScheduleWriter;

    const ENTITY_RMA = 'cj_rma';
    const ENTITY_ORDER = 'cj_sales_order';
    const ENTITY_REDEMPTION = 'cj_redemption';
    const ENTITY_REDEMPTION_POS = 'cj_redemption_pos';
    const ENTITY_CRON_SCHEDULE = 'cj_cron_schedule';
    const ENTITY_ORDER_DATAMARKETING = 'bigquery_order';
    const ENTITY_ORDER_ITEM_DATAMARKETING = 'bigquery_sales_order_item';

    /**
     * @var \Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv
     */
    private $gcrmOrderCsv;

    /**
     * @var \Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv
     */
    private $gcrmOrderitemCsv;

    /**
     * @param Export\Adapter\OrderCsv $orderCsv
     * @param Export\Adapter\RedemptionCsv $redemptionCsv
     * @param Export\Adapter\RedemptionPosCsv $redemptionPosCsv
     * @param Export\Adapter\RmaCsv $rmaCsv
     * @param Export\Adapter\CronScheduleCsv $cronScheduleCsv
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\ImportExport\Model\Export\Entity\Factory $entityFactory
     * @param \Magento\ImportExport\Model\Export\Adapter\Factory $exportAdapterFac
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $collectionFactory
     * @param \Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv $gcrmOrderCsv
     * @param \Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv $gcrmOrderitemCsv
     * @param array $data
     */
    public function __construct(
        \CJ\DataExport\Model\Export\Adapter\OrderCsv $orderCsv,
        \CJ\DataExport\Model\Export\Adapter\RedemptionCsv $redemptionCsv,
        \CJ\DataExport\Model\Export\Adapter\RedemptionPosCsv $redemptionPosCsv,
        \CJ\DataExport\Model\Export\Adapter\RmaCsv $rmaCsv,
        \CJ\DataExport\Model\Export\Adapter\CronScheduleCsv $cronScheduleCsv,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\ImportExport\Model\Export\Entity\Factory $entityFactory,
        \Magento\ImportExport\Model\Export\Adapter\Factory $exportAdapterFac,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        ResolverInterface $localeResolver,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $collectionFactory,
        \Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv $gcrmOrderCsv,
        \Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv $gcrmOrderitemCsv,
        array $data = []
    ) {
        parent::__construct(
            $objectManager,
            $eventManager,
            $serializer,
            $logger,
            $filesystem,
            $exportConfig,
            $entityFactory,
            $exportAdapterFac,
            $resultRedirectFactory,
            $coreDate,
            $localeResolver,
            $dataPersistor,
            $collectionFactory,
            $data
        );
        $this->dataPersistor = $dataPersistor;
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
        $this->cjRmaWriter = $rmaCsv;
        $this->cjOrderWriter = $orderCsv;
        $this->cjRedemptionWriter = $redemptionCsv;
        $this->cjRedemptionPosWriter = $redemptionPosCsv;
        $this->cjCronScheduleWriter = $cronScheduleCsv;
        $this->gcrmOrderCsv = $gcrmOrderCsv;
        $this->gcrmOrderitemCsv = $gcrmOrderitemCsv;
        $this->fileFormats = $this->_exportConfig->getFileFormats();
    }

    /**
     * {@inheritDoc}
     */
    public function export()
    {
        if (!isset($this->_data[self::FILTER_ELEMENT_GROUP])) {
            throw new LocalizedException(__('Please provide filter data.'));
        }

        switch ($this->getEntity()) {
            case self::ENTITY_REDEMPTION_POS:
            case self::ENTITY_REDEMPTION:
            case self::ENTITY_RMA:
            case self::ENTITY_ORDER:
            case self::ENTITY_CRON_SCHEDULE:
            case self::ENTITY_ORDER_DATAMARKETING:
            case self::ENTITY_ORDER_ITEM_DATAMARKETING:
                $this->_logger->log('info', __('Begin export of %1', $this->getEntity()));
                $fileFormat = $this->getFileFormat();
                if (!isset($this->fileFormats[$fileFormat])) {
                    throw new LocalizedException(__('Please correct the file format.'));
                }
                $adapter = $this->returnExport($this->fileFormats[$fileFormat]['model']);
                $entityAdapter = $this->_getEntityAdapter()->setWriter($adapter);

                $result = $entityAdapter->export();
                if (!$result) {
                    return false;
                }
                $countRows = substr_count(trim($result), "\n");
                if (!$countRows) {
                    throw new LocalizedException(__('There is no data for the export.'));
                }
                if ($result) {
                    $this->_logger->log('info', __('Exported %1 rows.', $countRows));
                    $this->_logger->log('info', __('The export is finished.'));
                }
                break;
            default:
                $result = parent::export();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function returnExport($className)
    {
        switch ($this->getEntity()) {
            case self::ENTITY_REDEMPTION:
                return $this->cjRedemptionWriter;
            case self::ENTITY_REDEMPTION_POS:
                return $this->cjRedemptionPosWriter;
            case self::ENTITY_RMA:
                return $this->cjRmaWriter;
            case self::ENTITY_ORDER:
                return $this->cjOrderWriter;
            case self::ENTITY_CRON_SCHEDULE:
                return $this->cjCronScheduleWriter;
            case self::ENTITY_ORDER_DATAMARKETING:
                return $this->gcrmOrderCsv;
            case self::ENTITY_ORDER_ITEM_DATAMARKETING:
                return  $this->gcrmOrderitemCsv;
            default:
                return parent::returnExport($className);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getScheduledFileName()
    {
        if ($this->getEntity() == CronScheduleInterface::ENTITY_TYPE) {
            $runDate = $this->getRunDate() ? $this->getRunDate() : null;
            return $this->_dateModel->date('Y-m-d', $runDate) . "-" . CronScheduleInterface::FILE_NAME;
        } else {
            return parent::getScheduledFileName();
        }
    }

    /**
     * @param string $lastOrderItem
     * @return void
     */
    public function updateExportTable($lastOrderItem = '')
    {
        $runDate = $lastOrderItem ?: ($this->getRunDate() ?: null);

        $entity = $this->getEntity();

        switch ($entity) {
            case self::ENTITY_ORDER:
            case self::ENTITY_RMA:
            case self::ENTITY_REDEMPTION:
            case self::ENTITY_REDEMPTION_POS:
            case self::ENTITY_CRON_SCHEDULE:
            case self::ENTITY_ORDER_DATAMARKETING:
            case self::ENTITY_ORDER_ITEM_DATAMARKETING:
                $date = $this->_dateModel->date('Y-m-d H:i:s', $runDate);
                $this->collectionFactory
                    ->create()
                    ->addFieldToFilter('entity_code', ['eq' => $entity])
                    ->getFirstItem()
                    ->setData('last_status', 1)
                    ->setData('updated_at', $date)
                    ->save();
                break;
            default:
                parent::updateExportTable($lastOrderItem);
        }
    }

}
