<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\ViewModel;

use Amasty\Shopby\Model\Layer\IsBrandPage;
use Amasty\Shopby\Model\Request;
use Amasty\ShopbyBase\Model\Detection\IsSearchPage;
use Amasty\ShopbyFilterAnalytics\Model\ConfigProvider;
use Amasty\ShopbyFilterAnalytics\Model\Filter\CollectUsedOptions;
use Amasty\ShopbyFilterAnalytics\Model\Source\EntityId;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FilterAnalytics implements ArgumentInterface
{
    public const REQUEST_URL_PATH = 'amshopbyfilteranalytics';

    /**
     * @var Request
     */
    private $shopbyRequest;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsSearchPage
     */
    private $isSearchPage;

    /**
     * @var IsBrandPage|null
     */
    private $isBrandPage;

    /**
     * @var CollectUsedOptions
     */
    private $collectUsedOptions;

    public function __construct(
        Request $shopbyRequest,
        Resolver $layerResolver,
        Json $json,
        UrlInterface $urlBuilder,
        ConfigProvider $configProvider,
        IsSearchPage $isSearchPage,
        IsBrandPage $isBrandPage,
        CollectUsedOptions $collectUsedOptions
    ) {
        $this->shopbyRequest = $shopbyRequest;
        $this->layerResolver = $layerResolver;
        $this->json = $json;
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
        $this->isSearchPage = $isSearchPage;
        $this->isBrandPage = $isBrandPage;
        $this->collectUsedOptions = $collectUsedOptions;
    }

    /**
     * @return bool
     */
    public function isNeedCollectStatistics(): bool
    {
        $options = $this->collectUsedOptions->execute($this->shopbyRequest->getRequestParams());

        return $this->configProvider->isAnalyticsEnabled() && !empty($options);
    }

    /**
     * @return string
     */
    public function getJsonFilterOptions(): string
    {
        if ($this->isSearchPage->execute()) {
            $id = EntityId::SEARCH_PAGE_ENTITY_ID;
        } elseif ($this->isBrandPage->execute()) {
            $id = EntityId::BRAND_PAGE_ENTITY_ID;
        } else {
            $id = $this->layerResolver->get()->getCurrentCategory()->getId();
        }

        return $this->json->serialize(
            [
                'option_ids' => $this->collectUsedOptions->execute($this->shopbyRequest->getRequestParams()),
                'entity_id' => $id
            ]
        );
    }

    /**
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->urlBuilder->getUrl(self::REQUEST_URL_PATH);
    }
}
