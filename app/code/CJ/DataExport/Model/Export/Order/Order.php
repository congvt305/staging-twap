<?php

namespace CJ\DataExport\Model\Export\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

/**
 * Class Order
 */
class Order
    extends \Magento\ImportExport\Model\Export\AbstractEntity
    implements OrderInterface
{
    protected $_frequency = 1440 * 60;

    protected $includeColumns = [
        'order_id' => 'increment_id',
        'purchase_date' => 'created_at',
        'grand_total' => 'grand_total',
        'status' => 'status',
        'shipping_information' => 'shipping_description'
    ];
    /**
     * @var array
     */
    protected $headColumnNames = [
        OrderInterface::ORDER_ID => OrderInterface::ORDER_ID,
        OrderInterface::PURCHASE_DATE => OrderInterface::PURCHASE_DATE,
        OrderInterface::GRAND_TOTAL => OrderInterface::GRAND_TOTAL,
        OrderInterface::STATUS => OrderInterface::STATUS,
        OrderInterface::SHIPPING_INFORMATION => OrderInterface::SHIPPING_INFORMATION,
        OrderInterface::PAYMENT_METHOD => OrderInterface::PAYMENT_METHOD,

    ];

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\OrderCsv
     */
    protected $orderWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Order\AttributeCollectionProvider
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
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderColFactory;
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
     * @param \CJ\DataExport\Model\Export\Adapter\OrderCsv $orderCsv
     * @param \CJ\DataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Amore\GcrmDataExport\Helper\Data $dataHelper
     * @param \CJ\DataExport\Logger\Logger $logger
     * @param \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderColFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        \CJ\DataExport\Model\Config\Config $configHelper,
        \CJ\DataExport\Model\Export\Adapter\OrderCsv $orderCsv,
        \CJ\DataExport\Model\Export\Order\AttributeCollectionProvider $attributeCollectionProvider,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amore\GcrmDataExport\Helper\Data $dataHelper,
        \CJ\DataExport\Logger\Logger $logger,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderColFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->orderWriter = $orderCsv;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->exportCollectionFactory = $exportCollectionFactory;
        $this->orderColFactory = $orderColFactory;
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

        $ordersData = $this->getOrdersData();

        if ($ordersData == null) {
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return false;
        }

        $index = 0;
        $headersData = [];

        foreach ($ordersData as $orders) {
            foreach ($orders as $singleOrder) {
                if ($index == 0) {
                    foreach (array_keys($singleOrder) as $key) {
                        $headersData[] = $key;
                        $index += 1;
                    }
                    $writer->setHeaderCols($headersData);
                }

                $writer->writeRow($singleOrder);
            }
        }
        return $writer->getContents();
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
    protected function getAllowStores() {
        $ids = [];
        foreach ($this->_storeManager->getStores() as $id => $store) {
            if ($this->configHelper->getModuleEnable($id)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function getOrdersData()
    {
        $itemRow = [];
        $collection = $this->_getEntityCollection();

        $cnt = 0;
        foreach ($collection as $item) {

            $itemData = $this->dataHelper->fixSingleRowData($item->getData());
            $itemRow[$item->getOrderId()][$cnt] = $itemData;
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

            if ($exportDate == "NULL") {
                /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collection */
                $collection = $this->orderColFactory->create();
            } else {
                $endDate = strtotime($exportDate) + $this->_frequency;
                $endDate = date('Y-m-d h:i:s', $endDate);
                /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
                $collection = $this->orderColFactory->create();
                $connection = $collection->getConnection();
                foreach ($this->includeColumns as $alias => $fieldName) {
                    $collection->addFieldToSelect($fieldName, $alias);
                }
                $collection->addFieldToFilter('main_table.store_id', ['in'=> $this->getAllowStores()]);
                $collection->addFieldToFilter('main_table.created_at', [
                    'from' => $exportDate,
                    'to' => $endDate
                ]);
                $collection->getSelect()
                    ->joinLeft(
                        [
                            'payment' => $connection->getTableName('sales_order_payment')
                        ],
                        'payment.parent_id=main_table.entity_id',
                        [
                            'payment_method' => 'method'
                        ]
                    );



            }
        } catch (\Exception $e) {
            $this->logger->log('info', $e->getMessage());
        }

        return $collection;
    }

    /**
     * Set Order Writer for CSV File
     *
     * @param \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter $orderWriter
     * @return $this
     */
    public function setWriter(AbstractAdapter $orderWriter)
    {
        $this->orderWriter = $orderWriter;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getWriter()
    {
        return $this->orderWriter;
    }
}
