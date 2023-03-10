<?php

namespace CJ\DataExport\Model\Export\Redemption;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

/**
 * Class Redemption
 */
class RedemptionPos
    extends \Magento\ImportExport\Model\Export\AbstractEntity
    implements RedemptionPosInterface
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
        'creation_time' => 'creation_time',
        'redeem_date' => 'redeem_date',
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
            'creation_time'
        ],
        [
            'main_table',
            'redeem_date',
            'redeem_date'
        ],
        [
            'main_table',
            'customer_name',
            'first_name'
        ],
        [
            'main_table',
            'last_name',
            'last_name'
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
        ],
        [
            'storeview',
            'name',
            'store_view'
        ],
        [
            'storeview',
            'store_id',
            'store_id'
        ],
        [
            'main_table',
            'email',
            'email'
        ],
        [
            'main_table',
            'address',
            'homeAddr'
        ],
        [
            'main_table',
            'postcode',
            'homeZip'
        ],
        [
            'main_table',
            'city',
            'homeCity'
        ],
        [
            'main_table',
            'region',
            'homeState'
        ]
    ];

    /**
     * @var array
     */
    protected $headColumnNames = [
        RedemptionPosInterface::REDEEM_ID => RedemptionPosInterface::REDEEM_ID,
        RedemptionPosInterface::REDEMPTION_CAMPAIGN_ID => RedemptionPosInterface::REDEMPTION_CAMPAIGN_ID,
        RedemptionPosInterface::REDEMPTION_CAMPAIGN_NAME => RedemptionPosInterface::REDEMPTION_CAMPAIGN_NAME,
        RedemptionPosInterface::REDEEM_DATE => RedemptionPosInterface::REDEEM_DATE,
        RedemptionPosInterface::NAME => RedemptionPosInterface::NAME,
        RedemptionPosInterface::TELEPHONE => RedemptionPosInterface::TELEPHONE,
        RedemptionPosInterface::STATUS => RedemptionPosInterface::STATUS,
        RedemptionPosInterface::STORE_COUNTER => RedemptionPosInterface::STORE_COUNTER,
    ];

    /**
     * @var \CJ\DataExport\Model\Export\Adapter\RedemptionPosCsv
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
     * @var \CJ\Middleware\Helper\Data
     */
    protected $middlewareHelper;

    /**
     * @inheritDoc
     */
    public function __construct(
        \CJ\DataExport\Model\Config\Config $configHelper,
        \Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory $counterCollectionFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CJ\DataExport\Model\Export\Adapter\RedemptionPosCsv $redemptionWriter,
        \CJ\DataExport\Model\Export\Redemption\AttributeCollectionProvider $attributeCollectionProvider,
        \Amore\GcrmDataExport\Helper\Data $dataHelper,
        \Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory $redemptionColFactory,
        \Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory $exportCollectionFactory,
        \CJ\DataExport\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \CJ\Middleware\Helper\Data $middlewareHelper,
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
        $this->middlewareHelper = $middlewareHelper;
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
        foreach ($this->_storeManager->getStores() as $id => $store) {
            if ($this->configHelper->getModuleEnable($id)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    public function export()
    {
        $writer = $this->getWriter();

        $redemptionsData = $this->getRedemptionsData();
        if ($redemptionsData == null) {
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
                $writer->writeRow($singleRedemption);
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
        return self::ENTITY_TYPE;
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
     * @return array
     */
    private function getRedemptionsData()
    {
        $itemRow = [];
        $collection = $this->_getEntityCollection();

        $cnt = 0;

        foreach ($collection as $item) {
            $rowData = $item->getData();
            // Add sales organization code, sales office code columns
            $storeId = $rowData['store_id'] ? (int)$rowData['store_id'] : '';
            $rowData['sales_organization_code'] = $this->middlewareHelper->getSalesOrganizationCode('store', $storeId);
            $rowData['sales_office_code'] = $this->middlewareHelper->getSalesOfficeCode('store', $storeId);
            unset($rowData['store_id']);

            $itemData = $this->dataHelper->fixSingleRowData($rowData);
            $itemRow[$item->getIncrementId()][$cnt] = $itemData;
            $cnt++;
        }
        return $itemRow;
    }

    /**
     * Set Order Writer for CSV File
     *
     * @param \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter $orderWriter
     * @return $this
     */
    public function setWriter(AbstractAdapter $redemptionWriter)
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
    protected function _getEntityCollection()
    {
        try {
            $customExportData = $this->exportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => self::ENTITY_TYPE])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');

            if ($exportDate == "NULL") {
                $collection = $this->counterCollectionFactory->create();
            } else {
                $collection = $this->counterCollectionFactory->create();
                $connection = $collection->getConnection();
                $collection
                    ->addFieldToFilter(
                        'main_table.store_id',
                        [$this->getAllowStores()])
                    ->addFieldToFilter('main_table.update_time', ['gteq' => $exportDate]);
                $collection
                    ->getSelect()
                    ->joinLeft(['redemption_store' => $connection->getTableName('eguana_redemption_store')],
                        'redemption_store.redemption_id = secondTable.redemption_id', [])
                    ->joinLeft(['storeview' => $connection->getTableName('store')],
                    'storeview.store_id = redemption_store.store_id', [])
                    ->joinRight(['counter' => $connection->getTableName('eguana_redemption_counter')],
                        'main_table.counter_id = counter.offline_store_id', [])
                    ->joinLeft(['storeinfo' => 'storeinfo'], 'storeinfo.entity_id = counter.offline_store_id', [
                        RedemptionPosInterface::STORE_COUNTER => 'title'
                    ])
                    ->group(['main_table.entity_id', 'storeview.name'])
                    ->order('main_table.entity_id DESC');
                $collection->getSelect()->setPart('COLUMNS', $this->_header);
            }

        } catch (\Exception $e) {
            $this->logger->log('info', $e->getMessage());
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getWriter()
    {
        return $this->redemptionWriter;
    }
}
