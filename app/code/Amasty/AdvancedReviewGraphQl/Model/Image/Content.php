<?php

namespace Amasty\AdvancedReviewGraphQl\Model\Image;

use Amasty\AdvancedReviewGraphQl\Api\Data\ImageContentInterface;
use Magento\Framework\Model\AbstractModel;

class Content extends AbstractModel implements ImageContentInterface
{

    /**
     * @inheritdoc
     */
    public function getBase64EncodedData()
    {
        return $this->_getData(ImageContentInterface::BASE64_ENCODED_DATA);
    }

    /**
     * @inheritdoc
     */
    public function setBase64EncodedData($base64EncodedData)
    {
        return $this->setData(ImageContentInterface::BASE64_ENCODED_DATA, $base64EncodedData);
    }

    /**
     * @inheritdoc
     */
    public function setNameWithExtension($nameWithExtension)
    {
        return $this->setData(ImageContentInterface::NAME, $nameWithExtension);
    }

    /**
     * @inheritdoc
     */
    public function getNameWithExtension()
    {
        return $this->_getData(ImageContentInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function getTemporaryName()
    {
        return $this->_getData(ImageContentInterface::TMP_NAME);
    }

    /**
     * @param string $tmpName
     *
     * @return ImageContentInterface
     */
    public function setTemporaryName($tmpName)
    {
        return $this->setData(ImageContentInterface::TMP_NAME, $tmpName);
    }
}
