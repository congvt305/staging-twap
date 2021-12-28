<?php

namespace CJ\DataExport\Model\Export\Redemption;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Redemption
 */
class Redemption
    extends \Magento\ImportExport\Model\Export\AbstractEntity
    implements RedemptionInterface
{

    /**
     * @var string[]
     */
    protected $columns = [
        'entity_id' => 'id',
        'email' => 'email',
        'telephone' => 'telephone',
        'status' => 'status',
        'utm_source' => 'utm_source',
        'utm_medium' => 'utm_medium',
        'utm_content' => 'utm_content',
        'creation_time' => 'redeem_date',
        'update_time' => 'update_time'
    ];

    protected $_header = [
        [
            'secondTable',
            'redemption_id',
            'redemption_campaign_id'
        ],
        [
            'secondTable',
            'title',
            'redemption_campaign_name'
        ],
        [
            'main_table',
            'creation_time',
            'redeem_date'
        ],
        [
            'main_table',
            'customer_name',
            'name'
        ],
        [
            'main_table',
            'telephone',
            'telephone'
        ],
        [
            'main_table',
            'status',
            'status'
        ],
        [
            'storeinfo',
            'title',
            'store_counter'
        ]
    ];

    /**
     * @var array
     */
    protected $headColumnNames = [
        RedemptionInterface::REDEEM_ID => RedemptionInterface::REDEEM_ID,
        RedemptionInterface::REDEMPTION_CAMPAIGN_ID => RedemptionInterface::REDEMPTION_CAMPAIGN_ID,
        RedemptionInterface::REDEMPTION_CAMPAIGN_NAME => RedemptionInterface::REDEMPTION_CAMPAIGN_NAME,
        RedemptionInterface::REDEEM_DATE => RedemptionInterface::REDEEM_DATE,
        RedemptionInterface::NAME => RedemptionInterface::NAME,
        RedemptionInterface::TELEPHONE => RedemptionInterface::TELEPHONE,
        RedemptionInterface::STATUS => RedemptionInterface::STATUS,
        RedemptionInterface::STORE_COUNTER => RedemptionInterface::STORE_COUNTER,
    ];

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RedemptionCsv
     */
    protected $redemptionWriter;

    /**
     * @var \CJ\DataExport\Model\Export\Redemption\AttributeCollectionProvider
     */
    protected $attributeCollectionProvider;

    /**
     * @var \Amore\GcrmDataExport\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory
     */
    protected $redemptionColFactory;

    /**
     * @var \Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory
     */
    protected $counterCollectionFactory;
    /**
     * @var \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory
     */
    private $exportCollectionFactory;

    /**
     * @var \CJ\DataExport\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \CJ\DataExport\Model\Config\Config
     */
    protected $configHelper;

    /**
     * @inheritDoc
     */
    public function __construct(
        \CJ\DataExport\Model\Config\Config $configHelper,
        \Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory $counterCollectionFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CJ\DataExport\Model\Export\Adapter\RedemptionCsv $redemptionWriter,
        \CJ\DataExport\Model\Export\Redemption\AttributeCollectionProvider $attributeCollectionProvider,
        \Amore\GcrmDataExport\Helper\Data $dataHelper,
        \Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory $redemptionColFactory,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory,
        \CJ\DataExport\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->counterCollectionFactory = $counterCollectionFactory;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->redemptionWriter = $redemptionWriter;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->dataHelper = $dataHelper;
        $this->redemptionColFactory = $redemptionColFactory;
        $this->exportCollectionFactory = $exportCollectionFactory;
        $this->logger = $logger;
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
    protected function getAllowStores()
    {
        $ids = [];
        if ($storeIds = $this->configHelper->getRedemptionStoreIds()) {
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
     * {@inheritDoc}
     */
    public function export()
    {
        $writer = $this->getRedemptionWriter();

        $redemptionsData = $this->getRedemptionsData();
        if ($redemptionsData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return false;
        }

        $index = 0;
        $headersData = [];

        foreach ($redemptionsData as $redemptions) {
            foreach ($redemptions as $singleRedemption) {
                if ($index == 0) {

                    foreach (\CJ\DataExport\Model\Export\Redemption\Data::$excludeColumns as $item) {
                        unset($singleRedemption[$item]);
                    }

                    foreach (array_keys($singleRedemption) as $key) {
                        $headersData[] = $key;
                        $index += 1;
                    }

                    $writer->setHeaderCols($headersData);
                }
                $writer->writeSourceRowWithCustomColumns($singleRedemption);
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
        return 'cj_redemption';
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
     * @return \CJ\DataExport\Model\Export\Adapter\RedemptionCsv|\Magento\ImportExport\Model\Export\Factory
     * @throws LocalizedException
     */
    private function getRedemptionWriter()
    {
        if (!$this->redemptionWriter) {
            throw new LocalizedException(__('Please specify the redemption writer.'));
        }
        return $this->redemptionWriter;
    }

    /**
     * @return array
     */
    private function getRedemptionsData()
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
     * Set Order Writer for CSV File
     *
     * @param \CJ\DataExport\Model\Export\Adapter\RedemptionCsv $orderWriter
     * @return $this
     */
    public function setRedemptionWriter(\CJ\DataExport\Model\Export\Adapter\RedemptionCsv $redemptionWriter)
    {
        $this->redemptionWriter = $redemptionWriter;
        return $this;
    }

    /**
     * This function gets attributes cllection
     *
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @return \Eguana\Redemption\Model\ResourceModel\Counter\Collection
     */
    private function joinedItemCollection()
    {
        try {
            $customExportData = $this->exportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'cj_redemption'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            $duration = $this->configHelper->getRedemptionDurationMinutes() * 60;

            if ($exportDate == "NULL") {
                $collection = $this->counterCollectionFactory->create();
            } else {
                $currentTime = time();
                $startDate = date("Y-m-d h:i:s", $currentTime - $duration);
                $endDate = date("Y-m-d h:i:s", $currentTime);
                $collection = $this->counterCollectionFactory->create();
                $connection = $collection->getConnection();
                $collection
                    ->addFieldToFilter(
                        'main_table.store_id',
                        [$this->getAllowStores()])
                    ->addFieldToFilter('main_table.update_time', [
                        'from' => $startDate,
                        'to' => $endDate
                    ]);
                $collection
                    ->getSelect()
                    ->joinLeft(['redemption_store' => $connection->getTableName('eguana_redemption_store')],
                        'redemption_store.redemption_id = secondTable.redemption_id', [])
                    ->joinRight(['counter' => $connection->getTableName('eguana_redemption_counter')],
                        'main_table.counter_id = counter.offline_store_id', [])
                    ->joinLeft(['storeinfo' => 'storeinfo'], 'storeinfo.entity_id = counter.offline_store_id', [
                        RedemptionInterface::STORE_COUNTER => 'title'
                    ])
                    ->group('main_table.entity_id')
                    ->order('main_table.entity_id DESC');

                $collection->getSelect()->setPart('COLUMNS', $this->_header);

            }

        } catch (\Exception $e) {
            $this->logger->log('info', $e->getMessage());
        }

        return $collection;
    }

}