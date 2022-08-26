<?php

namespace CJ\AmastyReview\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package CJ\AmastyReview\Helper
 */
class Data extends AbstractHelper
{
    const CUSTOM_DISPLAY_REVIEW_FIELD = 'custom_amasty_review/general/custom_display_review';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getCustomDisplayReview()
    {
        return $this->scopeConfig->getValue(self::CUSTOM_DISPLAY_REVIEW_FIELD, ScopeInterface::SCOPE_WEBSITE);
    }
}
