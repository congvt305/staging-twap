<?php

namespace CJ\DataExport\Model\Export\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RedemptionCsv
 */
class RedemptionCsv extends \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Source file handler.
     *
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_fileHandler;

    /**
     * @var \Eguana\Redemption\Api\RedemptionRepositoryInterface
     */
    protected $redemptionRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \CJ\DataExport\Model\Config\Config
     */
    protected $config;

    /**
     * @var \CJ\DataExport\Logger\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $_namePrefix = 'cj_redemption';

    /**
     * @param \CJ\DataExport\Logger\Logger $logger
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \CJ\DataExport\Model\Config\Config $config
     * @param \Eguana\Redemption\Api\RedemptionRepositoryInterface $redemptionRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param null $destination
     * @param string $destinationDirectoryCode
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function __construct(
        \CJ\DataExport\Logger\Logger $logger,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \CJ\DataExport\Model\Config\Config $config,
        \Eguana\Redemption\Api\RedemptionRepositoryInterface $redemptionRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Filesystem $filesystem,
        $destination = null,
        string $destinationDirectoryCode = DirectoryList::VAR_DIR
    ) {
        register_shutdown_function([$this, 'destruct']);
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->logger = $logger;
        $this->redemptionRepository = $redemptionRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->_directoryHandle = $filesystem->getDirectoryWrite($destinationDirectoryCode);
        if (!$destination) {
            $destination = uniqid($this->_namePrefix);
            $this->_directoryHandle->touch($destination);
        }
        if (!is_string($destination)) {
            throw new LocalizedException(
                __('The destination file path must be a string.')
            );
        }

        if (!$this->_directoryHandle->isWritable()) {
            throw new LocalizedException(__('The destination directory is not writable.'));
        }
        if ($this->_directoryHandle->isFile($destination) && !$this->_directoryHandle->isWritable($destination)) {
            throw new LocalizedException(__('Destination file is not writable'));
        }

        $this->_destination = $destination;

        $this->_init();
    }

    /**
     * Object destructor.
     *
     * @return void
     */
    public function destruct()
    {
        if (is_object($this->_fileHandler)) {
            $this->_fileHandler->close();
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overridden in child classes.
     *
     * @return $this
     */
    protected function _init()
    {
        $this->_fileHandler = $this->_directoryHandle->openFile($this->_destination, 'w');
        return $this;
    }

    /**
     * Get contents of export file
     *
     * @return string
     */
    public function getContents()
    {
        return $this->_directoryHandle->readFile($this->_destination);
    }

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/csv';
    }

    /**
     * Return file extension for downloading.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'csv';
    }

    /**
     * Set column names.
     *
     * @param array $headerColumns
     * @return $this
     * @throws \Exception
     */
    public function setHeaderCols(array $headerColumns)
    {
        if (null !== $this->_headerCols) {
            throw new LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }
            $this->_fileHandler->writeCsv(array_keys($this->_headerCols), $this->_delimiter, $this->_enclosure);
        }
        return $this;
    }

    /**
     * Get Values of header columns
     *
     * @param array $originCols
     * @return array
     */
    public function getArrayValue(array $originCols)
    {
        $originColumn = [];
        if ($originCols) {
            foreach ($originCols as $columnName) {
                $originColumn[$columnName] = false;
            }
        }
        return $originColumn;
    }

    /**
     * Write row data to source file.
     *
     * @param array $rowData
     * @return $this
     * @throws \Exception
     */
    public function writeRow(array $rowData)
    {
        foreach (\CJ\DataExport\Model\Export\Redemption\Data::$excludeColumns as $item) {
            unset($rowData[$item]);
        }

        $headersData = [];
        foreach (array_keys($rowData) as $key) {
            $headersData[] = $key;
        }
        $this->_fileHandler->writeCsv(
            array_merge(array_intersect_key($rowData, $this->getArrayValue($headersData))),
            $this->_delimiter,
            $this->_enclosure
        );
        return $this;
    }
}
