<?php
declare(strict_types=1);

namespace Eguana\EventManager\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class ConfigData extends AbstractHelper
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * This method is used in XML layout.
     * @return string
     */
    public function getCanonicalForAllEvent(): string
    {
        if ($this->_request->getParam('id')) {
            return $this->createLink(

                $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE) . preg_replace('/^\//', '', $this->_request->getUri()->getPath())
            );
        }

        return '';
    }

    /**
     * Creeate link
     *
     * @param $url
     * @return string
     */
    protected function createLink($url): string
    {
        return '<link rel="canonical" href="' . $url . '" />';
    }
}
