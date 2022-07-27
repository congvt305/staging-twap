<?php

namespace CJ\SFLocker\Model\Import;

use CJ\SFLocker\Model\Config\Source\StoreType;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

class SfStores extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const SOURCE_TABLE = 'inventory_source';
    const SOURCE_CODE = 'source_code';
    const NAME = 'name';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
    const COUNTRY_ID = 'country_id';
    const REGION = 'region';
    const CITY = 'city';
    const POSTCODE = 'postcode';
    const STREET = 'street';
    const ENABLED = 'enabled';
    const IS_PICKUP_LOCATION_ACTIVE = 'is_pickup_location_active';
    const STORE_TYPE = 'store_type';

    protected $_permanentAttributes = [self::SOURCE_CODE];
    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = false;
    protected $groupFactory;
    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::SOURCE_CODE,
        self::NAME,
        self::COUNTRY_ID,
        self::REGION,
        self::CITY,
        self::POSTCODE,
        self::STREET,
    ];


    protected $_validators = [];


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;
    protected $_resource;

    public function __construct(
        \Magento\Framework\Json\Helper\Data                   $jsonHelper,
        \Magento\ImportExport\Helper\Data                     $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection             $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper      $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils                 $string,
        ProcessingErrorAggregatorInterface                    $errorAggregator,
        \Magento\Customer\Model\GroupFactory                  $groupFactory
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->groupFactory = $groupFactory;
    }

    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'import-sf-store';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        if (!isset($rowData[self::SOURCE_CODE]) || empty($rowData[self::SOURCE_CODE])) {
            $this->addRowError('store code empty', $rowNum);
            return false;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }


    /**
     * Create Advanced price data from raw data.
     *
     * @return bool Result of operation.
     * @throws \Exception
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }

        return true;
    }

    /**
     * Save newsletter subscriber
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Replace newsletter subscriber
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Deletes newsletter subscriber data from raw data.
     *
     * @return $this
     */
    public function deleteEntity()
    {
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::SOURCE_CODE];
                    $listTitle[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listTitle) {
            $this->deleteEntityFinish(array_unique($listTitle), self::SOURCE_TABLE);
        }
        return $this;
    }

    /**
     * Save and replace newsletter subscriber
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError('Store code empty', $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowTtile = $rowData[self::SOURCE_CODE];
                $listTitle[] = $rowTtile;
                $entityList[$rowTtile][] = [
                    self::SOURCE_CODE => $rowData[self::SOURCE_CODE],
                    self::NAME => $rowData['shop_name'],
                    self::ENABLED => 1,
                    self::LATITUDE => $rowData[self::LATITUDE],
                    self::LONGITUDE => $rowData[self::LONGITUDE],
                    self::COUNTRY_ID => 'HK',
                    self::REGION => $rowData[self::REGION],
                    self::CITY => $rowData['district'],
                    self::POSTCODE => $rowData['area'],
                    self::STREET => $rowData['address'],
                    self::IS_PICKUP_LOCATION_ACTIVE => 1,
                    self::STORE_TYPE => StoreType::SF_STORE,
                ];
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listTitle) {
                    if ($this->deleteEntityFinish(array_unique($listTitle), self::SOURCE_TABLE)) {
                        $this->saveEntityFinish($entityList, self::SOURCE_TABLE);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList, self::SOURCE_TABLE);
            }
        }
        return $this;
    }

    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn);
            }
        }
        return $this;
    }

    protected function deleteEntityFinish(array $listTitle, $table)
    {
        if ($table && $listTitle) {
            try {
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    $this->_connection->quoteInto('source_code IN (?)', $listTitle)
                );
                return true;
            } catch (\Exception $e) {
                return false;
            }

        } else {
            return false;
        }
    }
}
