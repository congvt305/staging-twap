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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
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
        $this->orderItemsColFactory = $orderItemsColFactory;
        $this->cjExportConfig = $cjExportConfig;
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
        $storeEnable = [];
        foreach ($this->_storeManager->getStores() as $store) {
            if ($this->cjExportConfig->getModuleEnableDataMarketing($store->getId())) {
                $storeEnable[] = $store->getId();
            }
        }

        $collection = $this->orderItemsColFactory->create();
        $collection->addFieldToFilter('store_id', ['in' => $storeEnable]);
        return $collection->getData();
    }
}
