<?php

namespace CJ\DataExport\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

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
     * @var string
     */
    protected $tableEntity;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    const ENTITY_RMA = 'cj_rma';
    const ENTITY_ORDER = 'cj_sales_order';
    const ENTITY_REDEMPTION = 'cj_redemption';

    /**
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
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
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
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $collectionFactory,
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
            $dataPersistor,
            $collectionFactory,
            $data
        );
        $this->dataPersistor = $dataPersistor;
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function export()
    {
        $entityName = $this->getEntity();

        if ($entityName == self::ENTITY_REDEMPTION) {
            $this->tableEntity = self::ENTITY_REDEMPTION;
            $adapter = $this->getCjRedemptionWriter();
            return $this->_getEntityAdapter()->setRedemptionWriter($adapter)->export();
        } elseif ($entityName == self::ENTITY_RMA) {
            $this->tableEntity = self::ENTITY_RMA;
            $adapter = $this->getCjRmaWriter();
            return $this->_getEntityAdapter()->setRmaWriter($adapter)->export();
        } elseif ($entityName == self::ENTITY_ORDER) {
            $this->tableEntity = self::ENTITY_ORDER;
            $adapter = $this->getCjOrderWriter();
            return $this->_getEntityAdapter()->setOrderWriter($adapter)->export();
        } else {
            return parent::export();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function returnExport($className)
    {
        $entity = $this->getEntity();
        if ($entity == self::ENTITY_REDEMPTION) {
            return $this->objectManager->create(\CJ\DataExport\Model\Export\Adapter\RedemptionCsv::class);
        } elseif ($entity == self::ENTITY_RMA) {
            return $this->objectManager->create(\CJ\DataExport\Model\Export\Adapter\RmaCsv::class);
        } elseif ($entity == self::ENTITY_ORDER) {
            return $this->objectManager->create(\CJ\DataExport\Model\Export\Adapter\OrderCsv::class);
        } else {
            return parent::returnExport($className);
        }
    }

    /**
     * @return \CJ\DataExport\Model\Export\Adapter\RmaCsv
     * @throws LocalizedException
     */
    protected function getCjRmaWriter()
    {
        if (!$this->cjRmaWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->cjRmaWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }

                if (!$this->cjRmaWriter instanceof AbstractAdapter) {
                    throw new LocalizedException(
                        __(
                            'The adapter object must be an instance of %1.',
                            AbstractAdapter::class
                        )
                    );
                }
            } else {
                throw new LocalizedException(__('Please correct the file format.'));
            }
        }

        return $this->cjRmaWriter;
    }

    /**
     * @return \CJ\DataExport\Model\Export\Adapter\OrderCsv
     * @throws LocalizedException
     */
    public function getCjOrderWriter()
    {
        if (!$this->cjOrderWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->cjOrderWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }

                if (!$this->cjOrderWriter instanceof AbstractAdapter) {
                    throw new LocalizedException(
                        __(
                            'The adapter object must be an instance of %1.',
                            AbstractAdapter::class
                        )
                    );
                }
            } else {
                throw new LocalizedException(__('Please correct the file format.'));
            }
        }

        return $this->cjOrderWriter;
    }

    /**
     * @return \CJ\DataExport\Model\Export\Adapter\RedemptionCsv
     * @throws LocalizedException
     */
    protected function getCjRedemptionWriter()
    {
        if (!$this->cjRedemptionWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->cjRedemptionWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }

                if (!$this->cjRedemptionWriter instanceof AbstractAdapter) {
                    throw new LocalizedException(
                        __(
                            'The adapter object must be an instance of %1.',
                            AbstractAdapter::class
                        )
                    );
                }
            } else {
                throw new LocalizedException(__('Please correct the file format.'));
            }
        }

        return $this->cjRedemptionWriter;
    }

    /**
     * @param string $lastOrderItem
     * @return void
     */
    public function updateExportTable($lastOrderItem = '')
    {
        $runDate = $lastOrderItem ?: ($this->getRunDate() ?: null);
        $date = $this->_dateModel->date('Y-m-d H:i:s', $runDate);

        if ($this->tableEntity == self::ENTITY_ORDER) {
            $this->_updateExportTable(self::ENTITY_ORDER, $date);
        } elseif ($this->tableEntity == self::ENTITY_RMA) {
            $this->_updateExportTable(self::ENTITY_RMA, $date);
        } elseif ($this->tableEntity == self::ENTITY_REDEMPTION) {
            $this->_updateExportTable(self::ENTITY_REDEMPTION, $date);
        } else {
            parent::updateExportTable($lastOrderItem);
        }
    }

    /**
     * @param string $entityName
     * @param string $date
     * @return void
     */
    protected function _updateExportTable($entityName, $date)
    {
        try {
            $customExportData = $this->collectionFactory
                ->create()
                ->addFieldToFilter('entity_code', ['eq' => $entityName])
                ->getFirstItem();
            $customExportData->setData('last_status', 1);
            $customExportData->setData('updated_at', $date);
            $customExportData->save();
        } catch (\Exception $e) {
            $this->_logger->log('info', $e->getMessage());
        }
    }
}
