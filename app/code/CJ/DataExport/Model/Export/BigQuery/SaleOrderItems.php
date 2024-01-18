<?php
declare(strict_types=1);
namespace CJ\DataExport\Model\Export\BigQuery;

use Amore\GcrmDataExport\Helper\Data;
use Amore\GcrmDataExport\Model\Config\Config as ConfigHeler;
use Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv;
use Amore\GcrmDataExport\Model\Export\Order\AttributeCollectionProvider;
use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use CJ\DataExport\Model\Config\Config as CJConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SaleOrderItems extends \Amore\GcrmDataExport\Model\Export\OrderItems\OrderItems
{
    /**
     * @var CollectionFactory
     */
    private $orderItemsColFactory;

    /**
     * @var CJConfig
     */
    private $cjExportConfig;

    /**
     * @var ExportCollection
     */
    private $exportCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var ConfigHeler
     */
    private $configHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ExportCollection $exportCollectionFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $orderItemsColFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param OrderItemsCsv $orderItemsWriter
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param Data $dataHelper
     * @param ConfigHeler $configHelper
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     * @param CJConfig $cjExportConfig
     * @param array $data
     */
    public function __construct(
        ExportCollection $exportCollectionFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $orderItemsColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        OrderItemsCsv $orderItemsWriter,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        Data $dataHelper,
        ConfigHeler $configHelper,
        OrderCollectionFactory $orderCollectionFactory,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        CJConfig $cjExportConfig,
        array $data = []
    ) {
        $this->exportCollectionFactory = $exportCollectionFactory;
        $this->configHelper = $configHelper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->orderItemsColFactory = $orderItemsColFactory;
        $this->cjExportConfig = $cjExportConfig;
        $this->logger = $logger;
        parent::__construct(
            $exportCollectionFactory,
            $attributeCollectionProvider,
            $orderItemsColFactory,
            $scopeConfig,
            $storeManager,
            $orderItemsWriter,
            $collectionFactory,
            $resourceColFactory,
            $resultRedirectFactory,
            $messageManager,
            $dataHelper,
            $configHelper,
            $orderCollectionFactory,
            $dataPersistor,
            $logger,
            $data
        );
    }

    /**
     * This method is used to get the entity type
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return "bigquery_sales_order_item";
    }

    /**
     * Get joined Item Collection
     *
     * @return array|null
     */
    public function joinedItemCollection()
    {
        $orderItems = [];
        try {
            $storeEnable = [];
            foreach ($this->_storeManager->getStores() as $store) {
                if ($this->cjExportConfig->getModuleEnableDataMarketing($store->getId())) {
                    $storeEnable[] = $store->getId();
                }
            }
            $customExportData = $this->exportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'bigquery_sales_order_item'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');

            $saleOrderItemsLimit = $this->configHelper->getOrderItemsLimit();
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('store_id', ['in' => $storeEnable]);
            if ($saleOrderItemsLimit) {
                if ($exportDate != "NULL") {
                    $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
                    $collection->setOrder('updated_at', 'ASC');
                }
                $collection->getSelect()->limit($saleOrderItemsLimit);
                $i = 1;
                $size = count($collection);
                /** @var OrderCollection $order */
                foreach ($collection as $order) {
                    /** @var Collection $items */
                    foreach ($order->getAllItems() as $items) {
                        $orderItems[] = $items->getData();
                    }
                    if ($i == $size && $size == $saleOrderItemsLimit) {
                        $this->dataPersistor->set('lastOrder', $order->getUpdatedAt());
                    }
                    $i++;
                }
            } else {
                if ($exportDate != "NULL") {
                    $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
                }
                $orderItems = $collection->getData();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $orderItems;
    }
}
