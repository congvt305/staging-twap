<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 22
 * Time: 오후 2:25
 */

namespace Amore\CustomerRegistration\Block;

use Magento\Framework\View\Element\Template;

/**
 * Block class for POS setup during registration
 * Class POS
 *
 */
class POS extends Template
{
    /**
     * Retrieve SMS code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getSendCodeUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/code',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve SMS code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getVerifyCodeUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/verify',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve POS Verification URL
     *
     * @return string
     */
    public function getPOSVerificationUrl()
    {
        return $this->getUrl(
            'customerregistration/verification/pos',
            ['_secure' => true]
        );
    }
}
