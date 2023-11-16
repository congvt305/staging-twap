<?php

namespace Amasty\XmlSitemap\Model\Source\Page;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetBaseUrl
{
    /**
     * @var array
     */
    private $baseUrls = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function getBaseUrl(int $storeId): string
    {
        if (isset($this->baseUrls[$storeId])) {
            return $this->baseUrls[$storeId];
        }

        $store = $this->storeManager->getStore($storeId);
        $isSecure = $store->isUrlSecure();
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $isSecure);
        $baseUrl = rtrim($baseUrl, '/') . '/';
        $this->baseUrls[$storeId] = $baseUrl;

        return $baseUrl;
    }
}
