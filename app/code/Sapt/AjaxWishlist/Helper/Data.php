<?php

namespace Sapt\AjaxWishlist\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const AJAX_WISHLIST_ENABLED = 'wishlist/ajax_wishlist/status';
    /**
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeInterface
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeInterface;
    }

    /**
     * To Check the module status
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return (bool)$this->getConfigValue(self::AJAX_WISHLIST_ENABLED);
    }

    /**
     * Get configValue
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
