<?php

namespace Amasty\CPS\Plugin\ElasticSearch\Plugin\Framework\Search\Request;

use Amasty\ElasticSearch\Model\Config;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;

class Builder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Content
     */
    private $contentHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Amasty\ShopbyBrand\Helper\Content $contentHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $argument
     * @return array
     */
    public function aroundBeforeCreate($subject, \Closure $proceed, $argument)
    {
        if ($this->contentHelper->getCurrentBranding()
            && $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH) == Config::ELASTIC_SEARCH_ENGINE
            && !$this->scopeConfig->isSetFlag('cataloginventory/options/show_out_of_stock')
        ) {
            $argument->bind('stock_status', Stock::STOCK_IN_STOCK);
        }

        return [];
    }
}
