<?php
declare(strict_types=1);

namespace Eguana\Redemption\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Canonical
 *
 */
class Canonical extends AbstractHelper
{
    /**
     * This method is used in XML layout.
     * @return string
     */
    public function getCanonicalForAllRedemption(): string
    {
        if ($this->_request->getParam('redemption_id')) {
            return $this->createLink(
                $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE)  . preg_replace('/^\//', '', $this->_request->getUri()->getPath())
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
