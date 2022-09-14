<?php

namespace CJ\CustomCookie\Block\Html;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use CJ\CustomCookie\Helper\Data as HelperData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->scopeConfig =  $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get CMS Block Identifier
     *
     * @return string
     * @throws \Exception
     */
    public function getCookieTemplateIdentifier()
    {
        return $this->helperData->getCookieTemplateBlockId($this->getStoreId());
    }
    /**
     * Get current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get current website Id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Is enabled cookie popup
     *
     * @return int
     */
    public function isEnabledCookiePopup()
    {
        return $this->helperData->isEnabledCookiePopup($this->getStoreId());
    }

    /**
     * Get cookie Lifetime
     *
     * @return int
     */
    public function getCookieLifeTime()
    {
        return $this->helperData->getCookieLifeTime($this->getStoreId());
    }

}
