<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 1/7/21
 * Time: 5:57 PM
 */
namespace Amore\GcrmDataExport\Model\Scheduled;

use Amore\GcrmDataExport\Model\Scheduled\Operation\Data as DataAlias;
use Amore\GcrmDataExport\Model\Ftp;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\OperationInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\ScheduledImportExport\Model\Scheduled\Operation as OperationAlias;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Filesystem\Io\Ftp as FTPAlias;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Config\ValueFactory;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;

/**
 * Define operations to perform in cron
 * Class Operation
 */
class Operation extends OperationAlias
{
    /**
     * @var Ftp
     */
    private $customFtp;
    /**
     * Operation constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param GenericFactory $schedOperFactory
     * @param DataFactory $operationFactory
     * @param ValueFactory $configValueFactory
     * @param DateTime $dateModel
     * @param ScopeConfigInterface $scopeConfig
     * @param StringUtils $string
     * @param TransportBuilder $transportBuilder
     * @param FTPAlias $ftpAdapter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        GenericFactory $schedOperFactory,
        DataFactory $operationFactory,
        ValueFactory $configValueFactory,
        DateTime $dateModel,
        ScopeConfigInterface $scopeConfig,
        StringUtils $string,
        TransportBuilder $transportBuilder,
        FTPAlias $ftpAdapter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Ftp $customFtp,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $filesystem,
            $storeManager,
            $schedOperFactory,
            $operationFactory,
            $configValueFactory,
            $dateModel,
            $scopeConfig,
            $string,
            $transportBuilder,
            $ftpAdapter,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
        $this->customFtp = $customFtp;
    }

    /**
     * Save File in History
     * @param OperationInterface $operation
     * @param string $fileContent
     * @return bool|int
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function saveFileSource(OperationInterface $operation, $fileContent)
    {
        $operation->addLogComment(__('Save history file content "%1"', $this->getHistoryFilePath()));
        $this->_saveOperationHistory($fileContent);

        $fileInfo = $this->getFileInfo();
        $fileName = $operation->getCustomFileName() . '.' . $fileInfo['file_format'];
        try {
            $result = $this->writeData($fileInfo['file_path'] . '/' . $fileName, $fileContent);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'We couldn\'t write file "%1" to "%2" with the "%3" driver. ' . $e->getMessage(),
                    $fileName,
                    $fileInfo['file_path'],
                    $fileInfo['server_type']
                )
            );
        }
        $operation->addLogComment(__('Save file content'));

        return $result;
    }

    /**
     * Read Data from File
     * @param string $source
     * @param string $destination
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function readData($source, $destination)
    {
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $this->validateAdapterType();
        $fileInfo = $this->getFileInfo();
        if (Data::FTP_STORAGE == $fileInfo['server_type']) {
            $this->customFtp->open($this->_prepareIoConfiguration($fileInfo));

            $ls = $this->customFtp->customLs($fileInfo['file_name']);

            $result = $this->ftpAdapter->read($ls['id'], $tmpDirectory->getAbsolutePath($destination));
            $this->customFtp->rm($ls['id']);
        } else {
            $rootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
            if (!$rootDirectory->isExist($source)) {
                throw new LocalizedException(__('Import path %1 not exists', $source));
            }
            $contents = $rootDirectory->readFile($rootDirectory->getRelativePath($source));
            $result = $tmpDirectory->writeFile($destination, $contents);
        }
        if (!$result) {
            throw new LocalizedException(__('We can\'t read the file.'));
        }

        return $tmpDirectory->getAbsolutePath($destination);
    }

    /**
     * Write Data to File for FTP Server
     * @param string $filePath
     * @param string $fileContent
     * @return bool|int
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function writeData($filePath, $fileContent)
    {
        $this->validateAdapterType();
        $fileInfo = $this->getFileInfo();
        if (Data::FTP_STORAGE == $fileInfo['server_type']) {
            $this->customFtp->open($this->_prepareIoConfiguration($fileInfo));
            $filePath = '/' . trim($filePath, '\\/');
            $result = $this->ftpAdapter->write($filePath, $fileContent);
        } else {
            $rootDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            $result = $rootDirectory->writeFile($filePath, $fileContent);
        }

        return $result;
    }

    /**
     * this method is used to get the file source
     * @param OperationInterface $operation
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getFileSource(OperationInterface $operation)
    {
        $fileInfo = $this->getFileInfo();
        if (empty($fileInfo['file_name']) || empty($fileInfo['file_path'])) {
            throw new LocalizedException(
                __('We can\'t read the file source because the file name is empty.')
            );
        }
        $operation->addLogComment(__('Connecting to server'));
        $operation->addLogComment(__('Reading import file'));

        if (strpos($this->getEntityType(), 'tempostar') !== false) {
            $extension = 'csv';
        } else {
            $extension = pathinfo($fileInfo['file_name'], PATHINFO_EXTENSION);
        }

        $filePath = $fileInfo['file_name'];
        $filePath = rtrim($fileInfo['file_path'], '\\/') . '/' . $filePath;
        $tmpFile = DirectoryList::TMP . '/' .uniqid() . '.' . $extension;

        try {
            $tmpFilePath = $this->readData($filePath, $tmpFile);
        } catch (FileSystemException $e) {
            throw new LocalizedException(__('We can\'t read the import file.'));
        }
        $operation->addLogComment(__('Save history file content "%1"', $this->getHistoryFilePath()));
        $this->_saveOperationHistory($tmpFilePath);
        return $tmpFilePath;
    }

    /**
     * Return History File Path
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getHistoryFilePath()
    {
        $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $dirPath = self::LOG_DIRECTORY . self::FILE_HISTORY_DIRECTORY . $this->_dateModel->date('Y/m/d') . '/';
        $logDirectory->create($dirPath);

        $fileName = join('_', [$this->_getRunTime(), $this->getOperationType(), $this->getEntityType()]);

        $fileInfo = $this->getFileInfo();
        if (isset($fileInfo['file_format'])) {
            $extension = $fileInfo['file_format'];
        } elseif (isset($fileInfo['file_name'])) {
            if (strpos($this->getEntityType(), 'tempostar') !== false) {
                $extension = 'csv';
            } else {
                $extension = pathinfo($fileInfo['file_name'], PATHINFO_EXTENSION);
            }
        } else {
            throw new LocalizedException(__('Unknown file format'));
        }

        return $logDirectory->getAbsolutePath($dirPath . $fileName . '.' . $extension);
    }

    /**
     * Add operation to cron
     * @throws LocalizedException
     * @return $this
     */
    protected function _addCronTask()
    {
        $frequency = $this->getFreq();
        $time = $this->getStartTime();
        if (!is_array($time)) {
            $time = explode(':', $time);
        }
        $cronExprArray = [
            $this->getCrontabSettings($frequency, $time[1]),
            $this->getCrontabSettings($frequency, $time[0]),
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        $cronExprString = join(' ', $cronExprArray);
        $exprPath = $this->getExprConfigPath();
        $modelPath = $this->getModelConfigPath();
        try {
            /** @var ValueInterface $exprValue */
            $exprValue = $this->_configValueFactory->create()->load($exprPath, 'path');
            $oldCronExprString = $exprValue->getValue();
            if ($oldCronExprString != $cronExprString) {
                $exprValue->setValue($cronExprString)->setPath($exprPath)->save();
                $this->_cacheManager->clean(['crontab']);
            }

            $this->_configValueFactory->create()->load(
                $modelPath,
                'path'
            )->setValue(
                self::CRON_MODEL
            )->setPath(
                $modelPath
            )->save();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                __('We were unable to save the cron expression.')
            );
        }
        return $this;
    }

    /**
     * Get crontab settings
     * @param $frequency
     * @param $time
     * @return int|string
     */
    protected function getCrontabSettings($frequency, $time)
    {
        if ($frequency == DataAlias::CUSTOM_CRON) {
            if ($time == 0) {
                $crontabSettings = '*';
            } else {
                $crontabSettings = '*/' . intval($time);
            }
        } else {
            $crontabSettings = intval($time);
        }
        return $crontabSettings;
    }
}
