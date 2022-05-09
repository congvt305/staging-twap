<?php

namespace CJ\CustomCookie\Block\Html;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use CJ\CustomCookie\Helper\Data as HelperData;

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
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param array $data
     * @param CookieHelper|null $cookieHelper
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        array $data = [],
        ?CookieHelper $cookieHelper = null
    ) {
        $this->helperData = $helperData;
        $data['cookieHelper'] = $cookieHelper ?? ObjectManager::getInstance()->get(CookieHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Link to cookie restriction privacy policy page
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicyLink()
    {
        return $this->_urlBuilder->getUrl('privacy-policy-cookie-restriction-mode');
    }

    /**
     * Get CMS Block Identifier
     *
     * @return string
     * @throws \Exception
     */
    public function getCookieTemplateIdentifier()
    {
        return $this->helperData->getCookieTemplateBlockId();
    }

    /**
     * Is enabled cookie on browser
     *
     * @return bool
     */
    public function isEnabledCookieBrowser()
    {
        return $this->helperData->isEnabledCookieBrowser();
    }
}
