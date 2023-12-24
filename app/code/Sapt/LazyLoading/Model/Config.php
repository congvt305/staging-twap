<?php
declare(strict_types=1);

namespace Sapt\LazyLoading\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * Determines if pagebuilder images should be lazy loaded
     */
    const XML_PATH_PAGEBUILDER_LAZYLOADING_ENABLED = 'sapt_lazyloading/lazyloading/enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isLazyLoadingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAGEBUILDER_LAZYLOADING_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
