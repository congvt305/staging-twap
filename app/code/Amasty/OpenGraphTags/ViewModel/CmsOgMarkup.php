<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\ViewModel;

use Amasty\OpenGraphTags\Model\Attribute\CmsProcessor;
use Amasty\OpenGraphTags\Model\ConfigProvider;
use Magento\Cms\Helper\Page as CmsHelper;
use Magento\Cms\Model\Page;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class CmsOgMarkup implements ArgumentInterface
{
    /**
     * @var Page
     */
    private $cmsPage;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CmsHelper
     */
    private $cmsHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CmsProcessor
     */
    private $cmsProcessor;

    public function __construct(
        Page $cmsPage,
        ConfigProvider $configProvider,
        CmsHelper $cmsHelper,
        StoreManagerInterface $storeManager,
        CmsProcessor $cmsProcessor
    ) {
        $this->cmsPage = $cmsPage;
        $this->configProvider = $configProvider;
        $this->cmsHelper = $cmsHelper;
        $this->storeManager = $storeManager;
        $this->cmsProcessor = $cmsProcessor;
    }

    /**
     * @return bool
     */
    public function isShowBlock(): bool
    {
        $isShowOnHome = $this->configProvider->isEnabledOnHomePage() && $this->isHomePage();
        $isShopOnCms = $this->configProvider->isEnabledOnCmsPages() && !empty($this->cmsPage->getId());
        
        return $isShowOnHome || $isShopOnCms;
    }

    /**
     * @return bool
     */
    public function isHomePage(): bool
    {
        return $this->cmsPage->getIdentifier() === $this->configProvider->getHomePageIdentifier();
    }

    /**
     * @return string|null
     */
    public function getPageUrl(): ?string
    {
        $pageId =  $this->cmsPage->getId();

        return $this->cmsHelper->getPageUrl($pageId);
    }

    /**
     * @return string
     */
    public function getSiteName(): string
    {
        return $this->storeManager->getStore()->getName();
    }

    /**
     * @return string
     */
    public function getCmsMetaTitle(): string
    {
        if ($this->isHomePage()) {
            $metaTitle = $this->cmsPage->getData(Page::META_TITLE) ?? __('Home Page');
        } else {
            $metaTitle = $this->cmsProcessor->getCmsTitleAttributeValue($this->cmsPage);
        }

        return (string)$metaTitle;
    }

    /**
     * @return string
     */
    public function getCmsMetaDescription(): string
    {
        if ($this->isHomePage()) {
            $metaDescription = $this->cmsPage->getData(Page::META_DESCRIPTION) ?? '';
        } else {
            $metaDescription = $this->cmsProcessor->getCmsDescriptionAttributeValue($this->cmsPage);
        }

        return $metaDescription;
    }
}
