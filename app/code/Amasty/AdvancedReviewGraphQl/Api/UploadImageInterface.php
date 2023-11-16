<?php

namespace Amasty\AdvancedReviewGraphQl\Api;

interface UploadImageInterface
{
    /**
     * Upload File
     *
     * @param \Amasty\AdvancedReviewGraphQl\Api\Data\ImageContentInterface $fileContent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Amasty\AdvancedReviewGraphQl\Api\Data\ImageContentInterface
     */
    public function upload(\Amasty\AdvancedReviewGraphQl\Api\Data\ImageContentInterface $fileContent);
}
