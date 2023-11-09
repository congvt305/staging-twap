<?php

namespace CJ\DataExport\Model\Scheduled;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\Framework\Message\ManagerInterface;
use CJ\DataExport\Model\Export\CronSchedule\CronScheduleInterface;

/**
 * Class Operation
 */
class Operation
    extends \Amore\GcrmDataExport\Model\Scheduled\Operation
{
    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    protected $sftpAdapter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var array
     */
    protected $customType = [
        'cj_rma',
        'cj_redemption',
        'cj_redemption_pos',
        'cj_sales_order',
        'cj_cron_schedule',
        'bigquery_order',
        'bigquery_sales_order_item'
    ];

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var array
     */
    protected $customFreq = [
        \CJ\DataExport\Model\Config\Source\Frequency::CRON_HALF_HOURLY => '*/30 * * * *'
    ];

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory $schedOperFactory
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory $operationFactory
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Filesystem\Io\Ftp $ftpAdapter
     * @param \Magento\Framework\Filesystem\Io\Sftp $sftpAdapter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory $schedOperFactory,
        \Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory $operationFactory,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Io\Ftp $ftpAdapter,
        \Magento\Framework\Filesystem\Io\Sftp $sftpAdapter,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $data = []
    ) {

        parent::__construct(
            $date,
            $context,
            $registry,
            $filesystem,
            $storeManager,
            $schedOperFactory,
            $operationFactory,
            $configValueFactory,
            $dateModel,
            $scopeConfig,
            $logger,
            $string,
            $transportBuilder,
            $ftpAdapter,
            $sftpAdapter,
            $resource,
            $resourceCollection,
            $serializer,
            $data
        );
        $this->driverFile = $driverFile;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->date = $date;
        $this->sftpAdapter = $sftpAdapter;
        $this->messageManager = $messageManager;
    }

    /**
     * Write data to specific storage (FTP, local filesystem)
     *
     * @param string $filePath
     * @param string $fileContent
     * @return bool|int
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function writeData($filePath, $fileContent)
    {
        $this->validateAdapterType();
        $fileInfo = $this->getFileInfo();
        if (Data::FTP_STORAGE == $fileInfo['server_type']) {
            try {
                $this->ftpAdapter->open($this->_prepareIoConfiguration($fileInfo));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                throw $e;
            }

            $filePath = '/' . trim($filePath, '\\/');
            if ($this->getEntityType() == CronScheduleInterface::ENTITY_TYPE) {
                $filePath = isset($sftpArgs['filename_prefix']) ? $this->getFileNameCronScheduleExport($sftpArgs['filename_prefix']) : $this->getFileNameCronScheduleExport();
                //1. save to local file
                $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
                $fileContent = $this->saveLocalFile($fileContent, $logDirectory);
            }
            $result = $this->ftpAdapter->write($filePath, $fileContent);

        } elseif ('sftp' == $fileInfo['server_type']) {
            $sftpArgs = $this->_prepareIoConfiguration($fileInfo);
            if (isset($sftpArgs['user'])) {
                $sftpArgs['username'] = $sftpArgs['user'];
            }
            $filePath = trim($filePath, '\\/');
            $filePathArr = explode('/', $filePath);
            $sftpFileName = isset($sftpArgs['filename_prefix']) ?
                $this->assignFilenamePrefix($sftpArgs['filename_prefix']) :
                $filePathArr[count($filePathArr) - 1];

            $this->sftpAdapter->open($sftpArgs);
            $this->sftpAdapter->setAllowCreateFolders(true);
            if (isset($sftpArgs['path']) && $sftpArgs['path']) {
                $sftpFilePath = trim($sftpArgs['path'], '\\/');
                $this->sftpAdapter->cd($sftpFilePath);
            }
            if ($this->getEntityType() == CronScheduleInterface::ENTITY_TYPE) {
                $sftpFileName = isset($sftpArgs['filename_prefix']) ? $this->getFileNameCronScheduleExport($sftpArgs['filename_prefix']) : $this->getFileNameCronScheduleExport();
                //1. save to local file
                $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
                $fileContent = $this->saveLocalFile($fileContent, $logDirectory);
            }
            $result = $this->sftpAdapter->write($sftpFileName, $fileContent);

        } else {
            $rootDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            if ($this->getEntityType() == CronScheduleInterface::ENTITY_TYPE) {
                $fileContent = $this->_getCronSCheduleExportContent($fileContent, $rootDirectory, $filePath);
                $result = $rootDirectory->writeFile($filePath, $fileContent, "a+");
            } else {
                $result = $rootDirectory->writeFile($filePath, $fileContent);
            }
        }

        return $result;
    }

    /**
     * @param $fileNamePrefix
     * @return string
     */
    protected function assignFilenamePrefix($fileNamePrefix)
    {
        $date = $this->date->date('Ymd_His');
        return $fileNamePrefix . "_" . $date . ".csv";
    }

    /**
     * {@inheritDoc}
     */
    protected function _addCronTask()
    {
        // If the entity type is a custom type,  we will change logic.
        if (in_array($this->getData('entity_type'), $this->customType)) {
            $frequency = $this->getFreq();

            // Build the cron expression string
            if ($frequency == \CJ\DataExport\Model\Config\Source\Frequency::CRON_HALF_HOURLY) {
                $cronExprString = $this->customFreq[\CJ\DataExport\Model\Config\Source\Frequency::CRON_HALF_HOURLY];
            } else {
                $time = $this->getStartTime();
                if (!is_array($time)) {
                    $time = explode(':', $time);
                }
                $cronExprArray = [
                    (int)$time[1],
                    (int)$time[0],
                    $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*',
                    '*',
                    $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*',
                ];
                $cronExprString = join(' ', $cronExprArray);
            }
            $exprPath = $this->getExprConfigPath();
            $modelPath = $this->getModelConfigPath();
            try {
                /** @var \Magento\Framework\App\Config\ValueInterface $exprValue */
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

                // Show a notice message only if the admin user chooses the custom type of frequency - "HALF_HOURLY"
                if ($frequency == \CJ\DataExport\Model\Config\Source\Frequency::CRON_HALF_HOURLY) {
                    $this->messageManager->addSuccessMessage(__('You have selected freqency as every 30 minutes. Then the system will not need to care about the value of start time, and scheduled update will be run at even time, like 10:00 AM, 10:30 AM, 11:00 AM...'));
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                throw new LocalizedException(
                    __('We were unable to save the cron expression.')
                );
            }
            return $this;
        } else {
            // If entity type is not a custom type, we don't change any logic. The program will run like before
            return parent::_addCronTask();
        }
    }

    /**
     * Get the path to file backup cron log
     * @return string
     * @throws FileSystemException
     */
    protected function getFilePathCronScheduleExport()
    {
        $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        $dirPath = self::LOG_DIRECTORY . self::FILE_HISTORY_DIRECTORY . $this->_dateModel->date('Y/m/d') . '/';
        $path = $dirPath;
        $path .= $this->getFileNameCronScheduleExport();
        return $path;
    }

    /**
     * Get content for the cron schedule log
     * The logic is if the file export is created, the content will be added to the exist file, no create new file
     * In that case, header will not included
     * If the file export is not exist, create a new file and write data to that file
     *
     * @param $oldContent
     * @param $rootDirectory
     * @param $filePath
     * @return string
     * @throws FileSystemException
     */
    public function _getCronSCheduleExportContent($oldContent, $rootDirectory, $filePath) {
        $newContent = "";
        if (!$rootDirectory->isExist($filePath) || !$this->driverFile->fileGetContents($rootDirectory->getAbsolutePath($filePath))) {
            $newContent = $oldContent;
        } else {
            $flag = 0;
            foreach (explode("\n", trim($oldContent, "\n")) as $line) {
                if ($flag == 0) {
                    $flag = 1;
                    continue;
                }
                $newContent .= $line . "\n";
            }
        }
        return $newContent;
    }

    /**
     * Save content to the file on localsystem and return content
     * @param $oldContent
     * @param $rootDirectory
     * @return string
     * @throws FileSystemException
     */
    protected function saveLocalFile($oldContent, $logDirectory)
    {
        $newContent = "";
        $path = $this->getFilePathCronScheduleExport();
        if (!$logDirectory->isExist($path) || !$this->driverFile->fileGetContents($logDirectory->getAbsolutePath($path))) {
            $newContent = $oldContent;
        } else {
            $flag = 0;
            foreach (explode("\n", trim($oldContent, "\n")) as $line) {
                if ($flag == 0) {
                    $flag = 1;
                    continue;
                }
                $newContent .= $line . "\n";
            }
        }
        $logDirectory->writeFile($path, $newContent, "a+");
        return $this->driverFile->fileGetContents($logDirectory->getAbsolutePath($path));
    }

    /**
     * @param $prefix
     * @return string
     */
    protected function getFileNameCronScheduleExport($prefix='')
    {
        $fileName = $this->_dateModel->date('Y-m-d');
        $fileName .= "-" . CronScheduleInterface::FILE_NAME . $prefix;
        $fileName .= ".csv";
        return $fileName;
    }
}
