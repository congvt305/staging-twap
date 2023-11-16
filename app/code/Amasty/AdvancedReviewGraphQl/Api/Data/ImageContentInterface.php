<?php

namespace Amasty\AdvancedReviewGraphQl\Api\Data;

interface ImageContentInterface
{
    public const BASE64_ENCODED_DATA = 'base64_encoded_data';
    public const NAME = 'name_with_extension';
    public const TMP_NAME = 'temporary_name';

    /**
     * @return string
     */
    public function getBase64EncodedData();

    /**
     * @param string $base64EncodedData
     *
     * @return ImageContentInterface
     */
    public function setBase64EncodedData($base64EncodedData);

    /**
     * @param string $nameWithExtension
     *
     * @return ImageContentInterface
     */
    public function setNameWithExtension($nameWithExtension);

    /**
     * @return string
     */
    public function getNameWithExtension();

    /**
     * @return string|null
     */
    public function getTemporaryName();

    /**
     * @param string $tmpName
     *
     * @return ImageContentInterface
     */
    public function setTemporaryName($tmpName);
}
