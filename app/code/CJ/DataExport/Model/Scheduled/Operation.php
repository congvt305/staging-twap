<?php

namespace CJ\DataExport\Model\Scheduled;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\Framework\Message\ManagerInterface;
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
        'cj_sales_order'
    ];

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
            $result = $this->sftpAdapter->write($sftpFileName, $fileContent);
        } else {
            $rootDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            $result = $rootDirectory->writeFile($filePath, $fileContent);
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
}
