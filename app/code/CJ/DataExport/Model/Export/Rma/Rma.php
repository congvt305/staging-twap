<?php

namespace CJ\DataExport\Model\Export\Rma;

use CJ\DataExport\Model\Export\Order\Order;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Rma
 */
class Rma
    extends \Magento\ImportExport\Model\Export\AbstractEntity
    implements RmaInterface
{
    /**
     * @var array
     */
    protected $headColumnNames = [
        RmaInterface::RMA_ID => RmaInterface::RMA_ID,
        RmaInterface::REQUESTED_DATE => RmaInterface::REQUESTED_DATE,
        RmaInterface::ORDER_ID => RmaInterface::ORDER_ID,
        RmaInterface::SHIPPING_METHOD_NUMBER => RmaInterface::SHIPPING_METHOD_NUMBER,
        RmaInterface::PAYMENT_METHOD => RmaInterface::PAYMENT_METHOD
    ];

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RmaCsv
     */
    protected $rmaWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Rma\AttributeCollectionProvider
     */
    protected $attributeCollectionProvider;

    /**
     * @var \Amore\GcrmDataExport\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory
     */
    private $exportCollectionFactory;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    protected $rmaCollFactory;

    /**
     * @var \CJ\DataExport\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \CJ\DataExport\Model\Config\Config
     */
    protected $configHelper;

    /**
     * @var array
     */
    protected $_header = [
        [
            'main_table',
            'increment_id',
            'rma_id'
        ],
        [
            'main_table',
            'date_requested',
            'requested'
        ],
        [
            'main_table',
            'order_increment_id',
            'order_id'
        ],
        [
            'label',
            'track_number',
            'shipping_method_number'
        ],
        [
            'sales',
            'method',
            'payment_method'
        ]
    ];

    /**
     * @param \CJ\DataExport\Model\Export\Adapter\RmaCsv $rmaCsv
     * @param \CJ\DataExport\Model\Export\Rma\AttributeCollectionProvider $attributeCollectionProvider
     * @param \Amore\GcrmDataExport\Helper\Data $dataHelper
     * @param \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $importExportCollFactory
     * @param \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollFactory
     * @param \CJ\DataExport\Logger\Logger $logger
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        \CJ\DataExport\Model\Config\Config $configHelper,
        \CJ\DataExport\Model\Export\Adapter\RmaCsv $rmaCsv,
        \CJ\DataExport\Model\Export\Rma\AttributeCollectionProvider $attributeCollectionProvider,
        \Amore\GcrmDataExport\Helper\Data $dataHelper,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $importExportCollFactory,
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollFactory,
        \CJ\DataExport\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->rmaWriter = $rmaCsv;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->dataHelper = $dataHelper;
        $this->exportCollectionFactory = $importExportCollFactory;
        $this->rmaCollFactory = $rmaCollFactory;
        $this->logger = $logger;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $data
        );
    }

    /**
     * @return array
     */
    protected function getAllowStores() {
        $ids = [];
        if ($storeIds = $this->configHelper->getRmaStoreIds()) {
            if (strpos(',', $storeIds) !== -1) {
                $stores = explode(',', $storeIds);
                foreach ($stores as $store) {
                    $ids[] = $store;
                }
            }
        };
        return $ids;
    }

    /**
     * @inheritDoc
     */
    public function export()
    {
        $writer = $this->getRmaWriter();

        $rmasData = $this->getRmasData();
        if ($rmasData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return false;
        }

        $index = 0;
        $headersData = [];
        foreach ($rmasData as $orders) {
            foreach ($orders as $singleOrder) {
                if ($index == 0) {
                    unset($singleOrder['store_name']);
                    foreach (array_keys($singleOrder) as $key) {
                        $headersData[] = $key;
                        $index += 1;
                    }
                    $writer->setHeaderCols($headersData);
                }
                $writer->writeSourceRowWithCustomColumns($singleOrder);
            }
        }
        return $writer->getContents();
    }

    /**
     * {@inheritDoc}
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityTypeCode()
    {
        return 'cj_rma';
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    protected function _getEntityCollection()
    {
        // TODO: Implement _getEntityCollection() method.
    }

    /**
     * @return \CJ\DataExport\Model\Export\Adapter\RmaCsv
     * @throws LocalizedException
     */
    public function getRmaWriter()
    {
        if (!$this->rmaWriter) {
            throw new LocalizedException(__('Please specify the rma writer.'));
        }
        return $this->rmaWriter;
    }

    /**
     * @return array
     */
    private function getRmasData()
    {
        $itemRow = [];
        $collection = $this->joinedItemCollection();

        $cnt = 0;
        foreach ($collection as $item) {
            $itemData = $this->dataHelper->fixSingleRowData($item->getData());
            $itemRow[$item->getIncrementId()][$cnt] = $itemData;
            $cnt++;
        }
        return $itemRow;
    }

    /**
     * This function gets attributes collection
     *
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @return \Magento\Rma\Model\ResourceModel\Rma\Collection
     */
    private function joinedItemCollection()
    {
        try {
            $customExportData = $this->exportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'cj_rma'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            $duration = $this->configHelper->getRmaDurationMinutes() * 60;
            if ($exportDate == "NULL") {
                $collection = $this->rmaCollFactory->create();
            } else {
                $currentTime = time();
                $startDate = date("Y-m-d h:i:s", $currentTime - $duration);
                $endDate = date("Y-m-d h:i:s", $currentTime);
                /** @var \Magento\Rma\Model\ResourceModel\Rma\Collection $collection */
                $collection = $this->rmaCollFactory->create();
                $collection->addFieldToFilter('main_table.store_id', ['in' => $this->getAllowStores()]);
                $collection
                    ->addFieldToFilter('main_table.date_requested', [
                        'from' => $startDate,
                        'to' => $endDate
                    ])
                    ->getSelect()
                    ->joinLeft(['sales' => 'sales_order_payment'],
                        'sales.parent_id=main_table.order_id',
                        [
                            'method'
                        ]
                    )->joinLeft(['label' => 'magento_rma_shipping_label'],
                        'label.rma_entity_id=main_table.entity_id',
                        [
                            'track_number'
                        ]
                    );

                $collection->getSelect()->setPart('COLUMNS', $this->_header);

            }
        } catch (\Exception $e) {
            $this->logger->log('info', $e->getMessage());
        }
        return $collection;
    }

    /**
     * Set Order Writer for CSV File
     *
     * @param \CJ\DataExport\Model\Export\Adapter\RmaCsv $rmaWriter
     * @return $this
     */
    public function setRmaWriter(\CJ\DataExport\Model\Export\Adapter\RmaCsv $rmaWriter)
    {
        $this->rmaWriter = $rmaWriter;
        return $this;
    }
}
