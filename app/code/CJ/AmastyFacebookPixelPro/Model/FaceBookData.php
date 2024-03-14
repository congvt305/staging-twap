<?php
declare(strict_types=1);

namespace CJ\AmastyFacebookPixelPro\Model;

use Magento\Customer\Model\Session;

class FaceBookData
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        Session $customerSession
    ) {
        $this->cookieManager = $cookieManager;
        $this->customerSession = $customerSession;
    }

    /**
     * Extracts the FBP cookie from the PHP Request Context.
     * @return string
     */
    public function getFbp() {
        $fbp = null;
        if (!empty($this->cookieManager->getCookie('_fbp'))) {
            $fbp = $this->cookieManager->getCookie('_fbp');
        }

        return $fbp;
    }

    /**
     * Extracts the FBC cookie from the PHP Request Context.
     * @return string
     */
    public function getFbc() {
        $fbc = null;

        if (!empty($this->cookieManager->getCookie('_fbc'))) {
            $fbc = $this->cookieManager->getCookie('_fbc');
        }

        return $fbc;
    }

    /**
     * Get customer session
     *
     * @return Session
     */
    public function getCustomerSession() {
        return $this->customerSession;
}
}
