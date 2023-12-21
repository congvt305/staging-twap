<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider;

use Magento\Store\Model\StoreManagerInterface;

class GetStoreBaseUrl
{
    /**
     * [
     *  'store_id' => 'value',
     *  ...
     * ]
     *
     * @var array
     */
    private $baseUrls;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function execute(int $storeId): ?string
    {
        if ($this->baseUrls === null) {
            foreach ($this->storeManager->getStores() as $currentStoreId => $store) {
                $this->baseUrls[$currentStoreId] = rtrim($store->getBaseUrl(), '/') . '/';
            }
        }

        return $this->baseUrls[$storeId] ?? null;
    }
}
