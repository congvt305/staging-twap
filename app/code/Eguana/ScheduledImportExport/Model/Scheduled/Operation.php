<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */

namespace Eguana\ScheduledImportExport\Model\Scheduled;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class Operation extends \Magento\ScheduledImportExport\Model\Scheduled\Operation
{
    /**
     * @var Filesystem\Io\Sftp
     */
    private $sftpAdapter;

    /**
     * Operation constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory $schedOperFactory
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory $operationFactory
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Filesystem\Io\Ftp $ftpAdapter
     * @param Filesystem\Io\Sftp $sftpAdapter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ScheduledImportExport\Model\Scheduled\Operation\GenericFactory $schedOperFactory,
        \Magento\ScheduledImportExport\Model\Scheduled\Operation\DataFactory $operationFactory,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Io\Ftp $ftpAdapter,
        \Magento\Framework\Filesystem\Io\Sftp $sftpAdapter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $registry, $filesystem, $storeManager, $schedOperFactory, $operationFactory, $configValueFactory, $dateModel, $scopeConfig, $string, $transportBuilder, $ftpAdapter, $resource, $resourceCollection, $data, $serializer);
        $this->sftpAdapter = $sftpAdapter;
    }

    /**
     * Write data to specific storage (FTP, local filesystem)
     *
     * @param string $filePath
     * @param string $fileContent
     * @return bool|int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $this->sftpAdapter->open($sftpArgs);
            $this->sftpAdapter->setAllowCreateFolders(true);
            $filePath = trim($filePath, '\\/');
            $sftpFileInfo = explode('/', $filePath);
            $sftpFileName = $sftpFileInfo[count($sftpFileInfo) -1];
            if (count($sftpFileInfo) > 1) {
                $sftpFilePath = str_replace($sftpFileName, '', $filePath);
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
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
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
                throw new \Magento\Framework\Exception\LocalizedException(__('Import path %1 not exists', $source));
            }
            $contents = $rootDirectory->readFile($rootDirectory->getRelativePath($source));
            $result = $tmpDirectory->writeFile($destination, $contents);
        }
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t read the file.'));
        }

        return $tmpDirectory->getAbsolutePath($destination);
    }

}
