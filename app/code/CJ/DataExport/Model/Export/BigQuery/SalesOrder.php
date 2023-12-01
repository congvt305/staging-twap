<?php
declare(strict_types=1);

namespace CJ\DataExport\Model\Export\BigQuery;

use Amore\GcrmDataExport\Helper\Data;
use Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv;
use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use CJ\DataExport\Model\Config\Config as CJConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
class SalesOrder extends \Amore\GcrmDataExport\Model\Export\Order\SalesOrder
{
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /** Attribute collection name.
     * Used to resolve entity attribute collection.
     */
    const ATTRIBUTE_COLLECTION_NAME = Collection::class;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CJConfig
     */
    private $cjExportConfig;

    /**
     * @var CollectionFactory
     */
    private $orderColFactory;

    /**
     * @var ExportCollection
     */
    private $ExportCollectionFactory;

    /**
     * @param ExportCollection $ExportCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param \Amore\GcrmDataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $orderColFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param OrderCsv $orderWriter
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param TimezoneInterface $timezone
     * @param Data $dataHelper
     * @param \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig
     * @param CJConfig $cjExportConfig
     * @param array $data
     */
    public function __construct(
        ExportCollection $ExportCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        \Amore\GcrmDataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $orderColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        OrderCsv $orderWriter,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        TimezoneInterface $timezone,
        Data $dataHelper,
        \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig,
        CJConfig $cjExportConfig,
        array $data = []
    ) {
        $this->orderColFactory = $orderColFactory;
        $this->cjExportConfig = $cjExportConfig;
        $this->ExportCollectionFactory = $ExportCollectionFactory;
        parent::__construct(
            $ExportCollectionFactory,
            $orderRepository,
            $attributeCollectionProvider,
            $orderColFactory,
            $scopeConfig,
            $storeManager,
            $orderWriter,
            $collectionFactory,
            $resourceColFactory,
            $resultRedirectFactory,
            $messageManager,
            $timezone,
            $dataHelper,
            $gcrmConfig,
            $data
        );
    }

    /**
     * This function gets orders cllectionn
     *
     * @return Collection
     */
    public function joinedItemCollection()
    {
        $storeEnable = [];
        foreach ($this->_storeManager->getStores() as $store) {
            if ($this->cjExportConfig->getModuleEnableDataMarketing($store->getId())) {
                $storeEnable[] = $store->getId();
            }
        }
        $customExportData = $this->ExportCollectionFactory->create()
            ->addFieldToFilter('entity_code', ['eq' => 'bigquery_order'])->getFirstItem();
        $exportDate = $customExportData->getData('updated_at');
        $collection = $this->orderColFactory->create();
        $collection->getSelect()->reset(Select::COLUMNS)->columns(self::HEAD_COLUMNS_NAME);
        if ($exportDate != "NULL") {
            $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
        }

        $collection->addFieldToFilter('store_id', ['in' => $storeEnable]);
        return $collection;
    }

    /**
     * Get Entity Type Code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return "bigquery_order";
    }
}
