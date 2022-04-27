<?php

namespace Amasty\Feed\Model\Export\Adapter;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Amasty\Feed\Model\Config\Source\NumberFormat;
use Amasty\Feed\Model\Config\Source\StorageFolder;
use Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Content;
use Amasty\Feed\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class Csv extends \Magento\ImportExport\Model\Export\Adapter\Csv
{
    public const HTTP = 'http://';
    public const HTTPS = 'https://';

    /**
     * @var array
     */
    protected $csvField = [];

    /**
     * @var bool
     */
    protected $columnName;

    /**
     * @var string|null
     */
    protected $header;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var array
     */
    protected $rates;

    /**
     * @var string
     */
    protected $formatPriceCurrency;

    /**
     * @var int
     */
    protected $formatPriceCurrencyShow;

    /**
     * @var int
     */
    protected $formatPriceDecimals;

    /**
     * @var string
     */
    protected $formatPriceDecimalPoint;

    /**
     * @var string
     */
    protected $formatPriceThousandsSeparator;

    /**
     * @var string
     */
    protected $formatDate;

    /**
     * @var int|null
     */
    protected $page;

    /**
     * @var NumberFormat
     */
    private $numberFormat;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Escaper $escaper,
        NumberFormat $numberFormat,
        Config $config,
        File $file,
        $destination = null,
        $page = null
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->productRepository = $productRepository;
        $this->page = $page;
        $this->numberFormat = $numberFormat;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->config = $config;

        parent::__construct($filesystem, $destination);
        $this->escaper = $escaper;
    }

    protected function _init()
    {
        return $this;
    }

    public function initBasics($feed)
    {
        $enclosure = $feed->getCsvEnclosure();
        $delimiter = $feed->getCsvDelimiter();

        $enclosures = [
            'double_quote' => '"',
            'quote' => '\'',
            'space' => ' ',
            'none' => '/n'
        ];

        $this->_enclosure = isset($enclosures[$enclosure]) ? $enclosures[$enclosure] : '"';

        $delimiters = [
            'comma' => ',',
            'semicolon' => ';',
            'pipe' => '|',
            //phpcs:ignore
            'tab' => chr(9)
        ];

        $mode = $this->page == 0 ? 'w' : 'a';

        if ($this->config->getStorageFolder() == StorageFolder::VAR_FOLDER) {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } else {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        $directoryPath = $dir->getAbsolutePath($this->config->getFilePath());
        if (!$this->file->isDirectory($directoryPath)) {
            $this->file->createDirectory($directoryPath);
        }
        $this->_directoryHandle = $dir;
        $this->_fileHandler = $dir->openFile($this->_destination, $mode);

        $this->_delimiter = isset($delimiters[$delimiter]) ? $delimiters[$delimiter] : ',';

        $this->columnName = $feed->getCsvColumnName() == 1;

        $this->header = $feed->getCsvHeader();

        $this->csvField = $feed->getCsvField();

        $this->initPrice($feed);

        return $this;
    }

    public function initPrice($feed)
    {
        $decimals = $this->numberFormat->getAllDecimals();
        $separators = $this->numberFormat->getAllSeparators();

        $formatPriceDecimals = $feed->getFormatPriceDecimals();
        $formatPriceDecimalPoint = $feed->getFormatPriceDecimalPoint();
        $formatPriceThousandsSeparator = $feed->getFormatPriceThousandsSeparator();
        $formatDate = $feed->getFormatDate();

        $this->formatPriceCurrency = $feed->getFormatPriceCurrency();
        $this->formatPriceCurrencyShow = $feed->getFormatPriceCurrencyShow() == 1;

        $this->formatPriceDecimals = isset($decimals[$formatPriceDecimals]) ? $decimals[$formatPriceDecimals] : 2;
        $this->formatPriceDecimalPoint =
            isset($separators[$formatPriceDecimalPoint]) ? $separators[$formatPriceDecimalPoint] : '.';

        $this->formatPriceThousandsSeparator =
            isset($separators[$formatPriceThousandsSeparator]) ? $separators[$formatPriceThousandsSeparator] : ',';

        $this->formatDate = !empty($formatDate) ? $formatDate : "Y-m-d";
    }

    protected function _getFieldKey($field)
    {
        $postfix = isset($field['parent']) && $field['parent'] == 'yes' ? '|parent' : '';

        return $field['attribute'] . $postfix;
    }

    public function writeHeader()
    {
        $columns = [];

        foreach ($this->csvField as $idx => $field) {
            $this->_headerCols[$idx . "_idx"] = false;
            $columns[] = $field['header'];
        }

        if (!empty($this->header)) {
            $this->_fileHandler->write($this->header . "\n");
        }

        if ($this->columnName !== false) {
            $this->_fileHandler->writeCsv($columns, $this->_delimiter, $this->_enclosure);
        }

        return $this;
    }

    public function writeFooter()
    {
        return true;
    }

    public function setHeaderCols(array $headerColumns)
    {
        if (null !== $this->_headerCols) {
            throw new LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }
        }

        return $this;
    }

    public function writeDataRow(array &$rowData)
    {
        $writeRow = [];

        foreach ($this->csvField as $idx => $field) {
            if ($field['static_text']) {
                $value = $field['static_text'];
            } else {
                $fieldKey = $this->_getFieldKey($field);
                $value = isset($rowData[$fieldKey]) ? $rowData[$fieldKey] : '';
            }

            $value = $this->_modifyValue($field, $value);
            $value = $this->_formatValue($field, $value);

            $writeRow[$idx . "_idx"] = $value;
        }

        if (count($writeRow) > 0) {
            if ($this->_enclosure == '/n') {
                foreach ($writeRow as $inx => $val) {
                    $writeRow[$inx] = str_replace($this->_delimiter, "", $val);
                }
                $this->_fileHandler->write(implode($this->_delimiter, $writeRow) . "\n");

            } else {
                parent::writeRow($writeRow);
            }
        }

        return $this;
    }

    /**
     * @param array $field
     * @param string $value
     *
     * @return int|float|string
     */
    protected function _modifyValue($field, $value)
    {
        if (isset($field['modify']) && is_array($field['modify'])) {
            foreach ($field['modify'] as $modify) {

                $value = $this->_modify(
                    $value,
                    $modify['modify'],
                    isset($modify['arg0']) ? $modify['arg0'] : null,
                    isset($modify['arg1']) ? $modify['arg1'] : null
                );
            }
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $modify
     * @param string|null $arg0
     * @param string|null $arg1
     *
     * @return float|int|string
     */
    protected function _modify($value, $modify, $arg0 = null, $arg1 = null)
    {
        switch ($modify) {
            case Content::STRIP_TAGS:
                $value = strtr($value, ["\n" => '', "\r" => '']);
                $value = strip_tags($value);
                break;
            case Content::HTML_ESCAPE:
                $value = $this->escaper->escapeHtml($value);
                break;
            case Content::LOWERCASE:
                $value = $this->lowerCase($value);
                break;
            case Content::INTEGER:
                $value = (int)$value;
                break;
            case Content::LENGTH:
                $length = (int)$arg0;

                if ($arg0 != '') {
                    $value = function_exists("mb_substr")
                        ? mb_substr($value, 0, $length, "UTF-8") : substr($value, 0, $length);
                }
                break;
            case Content::PREPEND:
                $value = $arg0 . $value;
                break;
            case Content::APPEND:
                $value .= $arg0;
                break;
            case Content::REPLACE:
                $value = str_replace($arg0, $arg1, $value);
                break;
            case Content::UPPERCASE:
                $value = function_exists("mb_strtoupper")
                    ? mb_strtoupper($value, "UTF-8") : strtoupper($value);
                break;
            case Content::CAPITALIZE:
                $value = ucfirst($this->lowerCase($value));
                break;
            case Content::ROUND:
                if (is_numeric($value)) {
                    $value = round($value);
                }
                break;
            case Content::IF_EMPTY:
                if (!strlen($value)) {
                    $value = $arg0;
                }
                break;
            case Content::IF_NOT_EMPTY:
                if (strlen($value)) {
                    $value = $arg0;
                }
                break;
            case Content::FULL_IF_NOT_EMPTY:
                if (!strlen($value)) {
                    $value = $arg0;
                } else {
                    $value = $arg1;
                }
                break;

            case Content::TO_SECURE_URL:
                $this->replaceFirst($value, self::HTTP, self::HTTPS);
                break;
            case Content::TO_UNSECURE_URL:
                $this->replaceFirst($value, self::HTTPS, self::HTTP);
                break;
        }

        return $value;
    }

    /**
     * Replace the first occurrence of $first in $value to $replace
     *
     * @param string $value
     * @param string $origin
     * @param string $replace
     */
    private function replaceFirst(&$value, $origin, $replace)
    {
        if (strpos($value, $origin) === 0) {
            $value = substr_replace($value, $replace, 0, strlen($origin));
        }
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function lowerCase($value)
    {
        return function_exists("mb_strtolower") ? mb_strtolower($value, "UTF-8") : strtolower($value);
    }

    protected function _formatValue($field, $value)
    {
        $format = isset($field['format']) ? $field['format'] : 'as_is';

        switch ($format) {
            case 'as_is':
                break;
            case 'date':
                if (!empty($value)) {
                    $value = date($this->formatDate, strtotime($value));
                }
                break;
            case 'price':
                if (is_numeric($value)) {
                    $value = $value * $this->getCurrencyRate();
                    $value = number_format(
                        $value,
                        $this->formatPriceDecimals,
                        $this->formatPriceDecimalPoint,
                        $this->formatPriceThousandsSeparator
                    );

                    if ($this->formatPriceCurrencyShow && $this->formatPriceCurrency) {
                        $value .= ' ' . $this->formatPriceCurrency;
                    }
                }

                break;
            case 'integer':
                break;
        }

        return $value;
    }

    protected function getCurrencyRate()
    {
        if (!$this->rates) {
            $codes = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);
            $this->rates = $this->currencyFactory->create()->getCurrencyRates(
                $this->storeManager->getStore()->getBaseCurrency(),
                $codes
            );
        }

        return isset($this->rates[$this->formatPriceCurrency]) ? $this->rates[$this->formatPriceCurrency] : 1;

            //TODO
//        if (!$this->_rates) {
//            $this->_rates = $this->_currencyFactory->create()->getCurrencyRates(
//                $this->_storeManager->getStore()->getBaseCurrency(),
//                [$this->_formatPriceCurrency]
//            );
//        }
//
//        return isset($this->_rates[$this->_formatPriceCurrency]) ? $this->_rates[$this->_formatPriceCurrency] : 1;
    }

    /**
     * Method which caused files deleting on Magento 2.3.5 was redefined
     *
     * @return void
     */
    public function destruct()
    {
        if (is_object($this->_fileHandler)) {
            $this->_fileHandler->close();
        }
    }
}
