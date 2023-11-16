<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Plugin\Block;

use Amasty\SeoRichData\Model\DataCollector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\SeoRichData\Helper\Config as ConfigHelper;
use Magento\Catalog\Block\Breadcrumbs as CatalogBreadcrumbs;
use Magento\Theme\Block\Html\Breadcrumbs as HtmlBreadcrumbs;
use Magento\Framework\App\RequestInterface;

class Breadcrumbs
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DataCollector
     */
    protected $dataCollector;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DataCollector $dataCollector,
        ConfigHelper $configHelper,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dataCollector = $dataCollector;
        $this->configHelper = $configHelper;
        $this->request = $request;
    }

    /**
     * @param HtmlBreadcrumbs $subject
     * @param $key
     * @param $value
     */
    public function beforeAssign(
        HtmlBreadcrumbs $subject,
        $key,
        $value
    ) {
        if ($key == 'crumbs' && $this->configHelper->isBreadcrumbsEnabled()) {
            $this->dataCollector->setData('breadcrumbs', $value);
        }
    }

    public function beforeToHtml(HtmlBreadcrumbs $subject): void
    {
        if (!$subject->getLayout()->getBlock('breadcrumbs_0')
            && ($this->isCategoryPage() || $this->isProductViewPage())
        ) {
            $subject->getLayout()->createBlock(CatalogBreadcrumbs::class);
        }
    }

    private function isProductViewPage(): bool
    {
        return $this->request->getModuleName() == 'catalog'
            && $this->request->getControllerName() == 'product';
    }

    private function isCategoryPage(): bool
    {
        return $this->request->getModuleName() == 'catalog'
            && $this->request->getControllerName() == 'category';
    }
}
