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
    public $helperData;
    /**
     * @param Template\Context $context
     * @param array $data
     * @param CookieHelper|null $cookieHelper
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        HelperData $helperData,
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

    public function getCookieTemplateIdentifier()
    {
        return $this->helperData->getCmsBlockIdentifier();
    }

}
