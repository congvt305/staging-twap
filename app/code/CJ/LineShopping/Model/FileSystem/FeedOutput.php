<?php

namespace CJ\LineShopping\Model\FileSystem;

use CJ\LineShopping\Helper\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\LineShopping\Logger\Logger;

class FeedOutput
{
    const FILE_TYPE = 'json';
    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param Json $json
     * @param File $file
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(
        Logger $logger,
        Json $json,
        File $file,
        Filesystem $filesystem,
        Config $config
    ) {
        $this->logger = $logger;
        $this->json = $json;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * @param $type
     * @return array
     * @throws FileSystemException
     */
    public function get($type): array
    {
        $fileName = $this->config->getFileName($type) . '.' . self::FILE_TYPE;
        $directoryPath = trim($this->config->getPathLineStore(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $outputFilename = $directoryPath . $fileName;
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        return [
            'filename' => $fileName,
            'absolute_path' => $dir->getAbsolutePath($outputFilename),
            'content' => $dir->readFile($outputFilename),
            'mtime' => $dir->stat($outputFilename)['mtime']
        ];
    }

    /**
     * @param $type
     * @param $websiteId
     * @param $data
     * @return void
     */
    public function createJsonFile($type, $websiteId, $data)
    {
        try {
            if ($fileName = $this->config->getFileName($type, $websiteId)) {
                $directoryPath = trim($this->config->getPathLineStore($websiteId), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                $directoryPath = $dir->getAbsolutePath($directoryPath);
                if (!$this->file->isDirectory($directoryPath)) {
                    $this->file->createDirectory($directoryPath);
                }
                $outputFilename = $directoryPath . $fileName;
                $contents = json_encode($data, JSON_UNESCAPED_UNICODE);
                $dir->writeFile($outputFilename . '.' . self::FILE_TYPE, $contents);
            }
        } catch(Exception $exception) {
            $this->logger->error(Logger::EXPORT_FEED_DATA,
                [
                    'type' => 'Create Json File',
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
