<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 1/7/21
 * Time: 5:57 PM
 */
namespace Amore\GcrmDataExport\Model;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DataObject;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ScheduledImportExport\Model\Export as ExportAlias;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\ScheduledImportExport\Model\Scheduled\Operation;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Model\Export\Adapter\Factory;
use Magento\ImportExport\Model\Export\Entity\Factory as FactoryAlias;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;
use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory;

/**
 * Class to Create custom CSV file and export data into it
 *
 * Class Export
 */
class Export extends ExportAlias
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ManagerInterfaces
     */
    private $eventManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $tableEntity = '';

    /**
     * @var
     */
    private $orderWriter;

    /**
     * @var
     */
    private $orderItemsWriter;

    /**
     * @var
     */
    private $quoteWriter;

    /**
     * @var
     */
    private $quoteItemsWriter;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Export constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param ConfigInterface $exportConfig
     * @param FactoryAlias $entityFactory
     * @param Factory $exportAdapterFac
     * @param DateTime $coreDate
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        Filesystem $filesystem,
        ConfigInterface $exportConfig,
        FactoryAlias $entityFactory,
        Factory $exportAdapterFac,
        RedirectFactory $resultRedirectFactory,
        DateTime $coreDate,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $logger,
            $filesystem,
            $exportConfig,
            $entityFactory,
            $exportAdapterFac,
            $coreDate,
            $data
        );
        $this->eventManager = $eventManager;
        $this->dataPersistor = $dataPersistor;
        $this->serializer = $serializer;
        $this->resultRedirectFactory =$resultRedirectFactory;
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Run export through cron
     * @param Operation $operation
     * @return bool
     * @throws \Exception
     * @throws LocalizedException
     */
    public function runSchedule(Operation $operation)
    {
        try {
            $data = $this->export();
            if(!$data) {
                $this->dataPersistor->set('operation_status',false);
                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        $this->dataPersistor->set('operation_status',true);
        $test = $this;
        $result = $operation->saveFileSource($this, $data);
        $this->updateExportTable();
        return (bool)$result;
    }

    /**
     * Create File Name
     * @return string
     * @throws LocalizedException
     */
    public function getCustomFileName()
    {
        $runDate = $this->getRunDate() ? $this->getRunDate() : null;
        return $this->getEntity() . '_' . $this->getOperationType() . '_' .
            $this->_dateModel->date('Y-m-d_H-i-s', $runDate) . '_utf8';
    }

    /**
     * Export data.
     *
     * @return string
     * @throws LocalizedException
     */
    public function export()
    {
        if (isset($this->_data[self::FILTER_ELEMENT_GROUP])) {
            $this->addLogComment(__('Begin export of %1', $this->getEntity()));
            if ($this->getEntity() == 'order') {
                $this->tableEntity = 'order';
                $result = $this->_getEntityAdapter()->setOrderWriter($this->getOrderWriter())->export();
            } elseif ($this->getEntity() == 'sales_order_item') {
                $this->tableEntity = 'sales_order_item';
                $result = $this->_getEntityAdapter()->setOrderItemsWriter($this->getOrderItemsWriter())->export();
            } elseif ($this->getEntity() == 'quote') {
                $this->tableEntity = 'quote';
                $result = $this->_getEntityAdapter()->setQuoteWriter($this->getQuoteWriter())->export();
            } elseif ($this->getEntity() == 'quote_item') {
                $this->tableEntity = 'quote_item';
                $result = $this->_getEntityAdapter()->setQuoteItemsWriter($this->getQuoteItemsWriter())->export();
            } else {
                $result = $this->_getEntityAdapter()->setWriter($this->_getWriter())->export();
            }
            if (!$result) {
                return false;
            }
            $countRows = substr_count(trim($result), "\n");
            if (!$countRows) {
                throw new LocalizedException(__('There is no data for the export.'));
            }
            if ($result) {
                $this->addLogComment([__('Exported %1 rows.', $countRows), __('The export is finished.')]);
            }
            return $result;
        } else {
            throw new LocalizedException(__('Please provide filter data.'));
        }
    }

    /**
     * Get Order Writer
     *
     * @return AbstractAdapter
     * @throws LocalizedException
     */
    protected function getOrderWriter()
    {
        if (!$this->orderWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->orderWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->orderWriter instanceof AbstractAdapter) {
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
        return $this->orderWriter;
    }

    /**
     * This method is used to get order items writer
     *
     * @return AbstractAdapter
     * @throws LocalizedException
     */
    protected function getOrderItemsWriter()
    {
        if (!$this->orderItemsWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->orderItemsWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->orderItemsWriter instanceof AbstractAdapter) {
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
        return $this->orderItemsWriter;
    }

    /**
     * This method is used to get Quote writer
     *
     * @return AbstractAdapter
     * @throws LocalizedException
     */
    protected function getQuoteWriter()
    {
        if (!$this->quoteWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->quoteWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->quoteWriter instanceof AbstractAdapter) {
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
        return $this->quoteWriter;
    }

    /**
     * This method is used to get Quote items writer
     *
     * @return AbstractAdapter
     * @throws LocalizedException
     */
    protected function getQuoteItemsWriter()
    {
        if (!$this->quoteItemsWriter) {
            $fileFormats = $this->_exportConfig->getFileFormats();

            if (isset($fileFormats[$this->getFileFormat()])) {
                try {
                    $this->quoteItemsWriter = $this->returnExport($fileFormats[$this->getFileFormat()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->quoteItemsWriter instanceof AbstractAdapter) {
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
        return $this->quoteItemsWriter;
    }

    /**
     * Return object Of required CSV Class
     *
     * @param $className
     * @return mixed
     * @throws LocalizedException
     */
    public function returnExport($className)
    {
        $entity = $this->getEntity();
        if ($entity == 'order') {
            return $this->objectManager->create('\Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv');
        } elseif ($entity == 'sales_order_item') {
            return $this->objectManager->create('\Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv');
        } elseif ($entity == 'quote') {
            return $this->objectManager->create('\Amore\GcrmDataExport\Model\Export\Adapter\QuoteCsv');
        } elseif ($entity == 'quote_item') {
            return $this->objectManager->create('\Amore\GcrmDataExport\Model\Export\Adapter\QuoteItemsCsv');
        } else {
            return $this->objectManager->create($className);
        }
    }

    /**
     * Set Last exported date & time in database
     */
    public function updateExportTable()
    {
        $runDate = $this->getRunDate() ? $this->getRunDate() : null;
        $date = $this->_dateModel->date('Y-m-d H:i:s', $runDate);
        if ($this->tableEntity == 'order') {
            try {
                $customExportData = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_code', ['eq' => 'order'])->getFirstItem();
                $customDate = $customExportData->getData('updated_at');
                $customExportData->setData('last_status', 1);
                $customExportData->setData('updated_at', $date);
                $customExportData->save();
            } catch (Exception $e) {
                $e->getMessage();
            }
        } elseif ($this->tableEntity == 'sales_order_item') {
            try {
                $customExportData = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_code', ['eq' => 'sales_order_item'])->getFirstItem();
                $customDate = $customExportData->getData('updated_at');
                $customExportData->setData('last_status', 1);
                $customExportData->setData('updated_at', $date);
                $customExportData->save();
            } catch (Exception $e) {
                $e->getMessage();
            }
        } elseif ($this->tableEntity == 'quote') {
            try {
                $customExportData = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_code', ['eq' => 'quote'])->getFirstItem();
                $customDate = $customExportData->getData('updated_at');
                $customExportData->setData('last_status', 1);
                $customExportData->setData('updated_at', $date);
                $customExportData->save();
            } catch (Exception $e) {
                $e->getMessage();
            }
        } elseif ($this->tableEntity == 'quote_item') {
            try {
                $customExportData = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_code', ['eq' => 'quote_item'])->getFirstItem();
                $customDate = $customExportData->getData('updated_at');
                $customExportData->setData('last_status', 1);
                $customExportData->setData('updated_at', $date);
                $customExportData->save();
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
    }
}
