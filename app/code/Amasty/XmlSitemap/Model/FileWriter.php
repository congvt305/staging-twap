<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Generator;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write as FileWrite;

class FileWriter implements WriterInterface
{
    /**
     * @var DirectoryWrite
     */
    private $directory;

    /**
     * @var FileWrite
     */
    private $stream;

    /**
     * @var FileWrite
     */
    private $previousStream;

    /**
     * @var int|bool
     */
    private $maxFileSize = false;

    /**
     * @var int|bool
     */
    private $maxItemsCount = false;

    /**
     * @var string[]
     */
    private $files = [];

    /**
     * Used for split files numeration.
     *
     * @var int
     */
    private $counter = 1;

    /**
     * @var null|string
     */
    private $originalPath = null;

    public function __construct(
        Filesystem $filesystem,
        array $writerConfig
    ) {
        $this->directory = $filesystem->getDirectoryWrite(AppDirectoryList::ROOT);
        $this->applyConfig($writerConfig);
    }

    public function open(string $filePath): void
    {
        if ($this->originalPath === null) {
            $this->originalPath = $filePath;
        }
        $absolutePath = $this->getAbsoluteFilePath($filePath);
        $this->stream = $this->directory->openFile($absolutePath);
        $this->files[] = $filePath;
    }

    public function openWithSaveStream(string $filePath): void
    {
        if ($this->stream !== null) {
            $this->previousStream = clone $this->stream;
        }
        $this->open($filePath);
    }

    private function getAbsoluteFilePath(string $filePath): string
    {
        return $this->directory->getAbsolutePath() . trim($filePath, '/');
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function write(Generator $data, array $parts): void
    {
        $itemsCount = 0;

        $header = $parts[self::PART_HEADER];
        $footer = $parts[self::PART_FOOTER];

        $fileSize = $this->stream->write($header);
        foreach ($data as $xmlString) {
            if ($this->previousStream) {
                $this->previousStream->write($footer);
                $this->previousStream->close();
                $this->previousStream = null;
                $fileSize = $this->stream->write($header);
            }

            if ($this->maxFileSize !== false) {
                $fileSize += strlen($xmlString);

                if ($this->maxFileSize < $fileSize) {
                    $this->stream->write($footer);
                    $this->changeFile();

                    $itemsCount = $this->maxItemsCount ? 0 : false;
                    $fileSize = $this->stream->write($header);
                    $fileSize += $this->stream->write($xmlString);
                } else {
                    $this->stream->write($xmlString);
                }
            } else {
                $this->stream->write($xmlString);
            }

            if ($this->maxItemsCount !== false) {
                ++$itemsCount;

                if ($this->maxItemsCount <= $itemsCount) {
                    $this->stream->write($footer);
                    $this->changeFile();
                    $itemsCount = 0;
                    $fileSize = $this->stream->write($header);
                }
            }
        }
        $this->stream->write($footer);
        $this->stream->close();
    }

    public function writeIndex(Generator $data, array $parts): void
    {
        $this->open($this->getIndexFilename());
        $this->clearConfig();
        $this->write($data, $parts);
    }

    public function shouldCreateIndexFile(): bool
    {
        return count($this->files) > 1;
    }

    public function removeIndexIfExists(): bool
    {
        $absolutePath = $this->getAbsoluteFilePath($this->getIndexFilename());
        if ($this->directory->isExist($absolutePath)) {
            try {
                $this->directory->delete($absolutePath);
            } catch (\Magento\Framework\Exception\FileSystemException $ex) {
                return false;
            }

            return true;
        }
        return false;
    }

    private function changeFile(): void
    {
        $filePath = end($this->files);

        if ($this->counter === 1) {
            $newFilePath = $this->addNumerationToFilename($filePath, 1);
            $this->renameFile($filePath, $newFilePath);
            $this->files[count($this->files) - 1] = $newFilePath;
            $filePath = $newFilePath;
        }
        $this->counter++;
        $newFilePath = $this->addNumerationToFilename($filePath, $this->counter);

        $this->stream->close();
        $this->open($newFilePath);
    }

    private function renameFile(string $oldPath, string $newFilePath): void
    {
        $this->directory->renameFile(
            $this->getAbsoluteFilePath($oldPath),
            $this->getAbsoluteFilePath($newFilePath)
        );
    }

    public function renameOriginalFile(string $newFilePath): bool
    {
        if ($this->files[0] === $this->originalPath) {
            $this->renameFile($this->files[0], $newFilePath);
            $this->files[0] = $newFilePath;

            return true;
        }

        return false;
    }

    private function getIndexFilename(): string
    {
        return str_replace('.xml', '_index.xml', $this->originalPath);
    }

    private function addNumerationToFilename(string $fileName, int $index): string
    {
        $addNumeration = function () use ($fileName, $index) {
            return str_replace('.xml', sprintf('_%d.xml', $index), $fileName);
        };
        if ($index === 1) {
            return $addNumeration();
        }

        $fileName = preg_replace('@(.*?_)\d+(.xml)@', sprintf('${1}%d$2', $index), $fileName, 1, $matched);
        if ($matched) {
            return $fileName;
        }

        return $addNumeration();
    }

    private function applyConfig(array $writerConfig): void
    {
        if ($writerConfig['max_file_size'] > 0) {
            $this->maxFileSize = $writerConfig['max_file_size'] * 1024;
        }

        if ($writerConfig['max_urls'] > 0) {
            $this->maxItemsCount = $writerConfig['max_urls'];
        }
    }

    private function clearConfig(): void
    {
        $this->maxFileSize = false;
        $this->maxItemsCount = false;
    }

    /**
     * Set counter for initial value.
     * Needed when files separated by group (e.x. by entity).
     */
    public function resetCounter(): void
    {
        $this->counter = 1;
    }
}
