<?php

namespace Amasty\AdvancedReviewGraphQl\Model;

use Amasty\AdvancedReview\Helper\ImageHelper;
use Amasty\AdvancedReviewGraphQl\Api\UploadImageInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;

class UploadImage implements UploadImageInterface
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $adapterFactory
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @inheritdoc
     */
    public function upload(\Amasty\AdvancedReviewGraphQl\Api\Data\ImageContentInterface $fileContent)
    {
        //phpcs:ignore
        if (!($file = base64_decode($fileContent->getBase64EncodedData()))) {
            throw new LocalizedException(__('Base64Decode File Error'));
        }

        $tmpPath = ImageHelper::IMAGE_TMP_PATH;
        $path = $this->mediaDirectory->getAbsolutePath($tmpPath);
        $name = $fileContent->getNameWithExtension();

        $fileContent->setBase64EncodedData(null);
        $fileContent->setNameWithExtension(null);

        if (!$this->mediaDirectory->isExist($path)) {
            $this->mediaDirectory->create($path);
        }
        $this->mediaDirectory->getDriver()->filePutContents(
            $path . DIRECTORY_SEPARATOR . $name,
            $file
        );

        $imageAdapter = $this->adapterFactory->create();
        $validateImage = $imageAdapter->validateUploadFile($path . DIRECTORY_SEPARATOR . $name);
        if ($validateImage && $this->mediaDirectory->isExist($path . DIRECTORY_SEPARATOR . $name)) {
            $fileContent->setTemporaryName($name);
        }

        return $fileContent;
    }
}
