<?php

declare(strict_types=1);

namespace CJ\PageBuilder\Model\Config;

/**
 * Class Config
 */
class Config
{
    const XML_PATH_EN_NAME_VISIBLE = 'cj_pagebuilder/carousel/en_product_name_visible';

    const XML_PATH_DESC_VISIBLE = 'cj_pagebuilder/carousel/desc_visible';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * @param $path
     * @param $type
     * @param $storeId
     * @return string
     */
    public function getValue($path, $type, $storeId): string
    {
        return $this->scopeConfig->getValue($path, $type, $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     * @return bool
     */
    public function getEnglishProductNameVisible($type, $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_EN_NAME_VISIBLE, $type, $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     * @return bool
     */
    public function getProductDescriptionVisible($type, $storeId = null): bool
    {
        return (bool)$this->getValue(self::XML_PATH_DESC_VISIBLE, $type, $storeId);
    }
}
