<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ResourceConnection;
use Amore\GcrmDataExport\Model\Config\Config;
use Amore\GcrmDataExport\Logger\Logger;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class to setup csv file
 *
 * Class QuoteItemsCsv
 */
class QuoteItemsCsv extends AbstractAdapter
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
     * @var Write
     */
    protected $_fileHandler;

    /**
     * @var CartItemRepositoryInterface
     */
    private $quoteItemRepositoryInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * QuoteItemsCsv constructor.
     * @param Logger $logger
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     * @param CartItemRepositoryInterface $quoteItemRepositoryInterface
     * @param TimezoneInterface $timezoneInterface
     * @param Filesystem $filesystem
     * @param null $destination
     * @param string $destinationDirectoryCode
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function __construct(
        Logger $logger,
        ResourceConnection $resourceConnection,
        Config $config,
        CartItemRepositoryInterface $quoteItemRepositoryInterface,
        TimezoneInterface $timezoneInterface,
        Filesystem $filesystem,
        $destination = null,
        $destinationDirectoryCode = DirectoryList::VAR_DIR
    ) {
        register_shutdown_function([$this, 'destruct']);
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->logger = $logger;
        $this->quoteItemRepositoryInterface = $quoteItemRepositoryInterface;
        $this->timezoneInterface = $timezoneInterface;
        $this->_directoryHandle = $filesystem->getDirectoryWrite($destinationDirectoryCode);
        if (!$destination) {
            $destination = uniqid('QuoteItems_');
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
     * @throws \Exception
     * @return $this
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
     * Write row data to source file.
     *
     * @param array $rowData
     * @param array $originColumnData
     * @return $this
     * @throws FileSystemException
     */
    public function writeSourceRowWithCustomColumns(array $rowData, array $originColumnData)
    {
        $index = 0;
        foreach ($originColumnData as $key) {
            if (!array_key_exists($key, $rowData)) {
                $rowData = array_slice($rowData,0, $index, true) + array($key => '') +
                    array_slice($rowData, $index, count($rowData), true) ;
            }
            $index++;
        }
        $this->_fileHandler->writeCsv(
            array_merge(array_intersect_key($rowData, $this->getArrayValue($originColumnData))),
            $this->_delimiter,
            $this->_enclosure
        );

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
     * @throws \Exception
     * @return $this
     */
    public function writeRow(array $rowData)
    {
       //Abstract method
    }
}
