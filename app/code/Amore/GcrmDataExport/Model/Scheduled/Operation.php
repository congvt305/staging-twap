<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 7/30/21
 * Time: 5:05 AM
 */

namespace Amore\GcrmDataExport\Model\Scheduled;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\Context;
use Magento\ScheduledImportExport\Model\Scheduled\Operation as OperationAlias;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * This class controls the data export to local or remote server
 *
 * Class Operation
 */
class Operation extends OperationAlias
{
    /**
     * @var Filesystem\Io\Sftp
     */
    private $sftpAdapter;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @param DateTime $date
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
     * @param Ftp $ftpAdapter
     * @param Sftp $sftpAdapter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        DateTime $date,
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
        Ftp $ftpAdapter,
        Sftp $sftpAdapter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Json $serializer = null,
        array $data = []
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
        $this->sftpAdapter = $sftpAdapter;
        $this->date = $date;
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
            $this->ftpAdapter->open($this->_prepareIoConfiguration($fileInfo));
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
                $filePathArr[count($filePathArr) -1];

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
     * Read data from specific storage (FTP, local filesystem)
     *
     * @param string $source
     * @param string $destination
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function readData($source, $destination)
    {
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $this->validateAdapterType();
        $fileInfo = $this->getFileInfo();
        if (Data::FTP_STORAGE == $fileInfo['server_type']) {
            $this->ftpAdapter->open($this->_prepareIoConfiguration($fileInfo));
            $source = '/' . trim($source, '\\/');
            $result = $this->ftpAdapter->read($source, $tmpDirectory->getAbsolutePath($destination));
        } elseif ('sftp' == $fileInfo['server_type']) {
            $sftpArgs = $this->_prepareIoConfiguration($fileInfo);
            if (isset($sftpArgs['user'])) {
                $sftpArgs['username'] = $sftpArgs['user'];
            }
            $this->sftpAdapter->open($sftpArgs);
            $source = '/' . trim($source, '\\/');
            $result = $this->sftpAdapter->read($source, $tmpDirectory->getAbsolutePath($destination));
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
     * This function manually assigns the user defiend prefix to file path
     *
     * @param $fileNamePrefix
     * @return string
     */
    private function assignFilenamePrefix($fileNamePrefix)
    {
        $date = $this->date->date('Ymd_His');
        $updatedFilePath = $fileNamePrefix . "_" . $date . ".csv";

        return $updatedFilePath;
    }
}

