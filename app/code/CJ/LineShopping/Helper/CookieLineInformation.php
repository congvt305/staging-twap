<?php

namespace CJ\LineShopping\Helper;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;

class CookieLineInformation
{
    const LINE_SHOPPING_ECID_COOKIE_NAME = 'line_ecid';
    const LINE_SHOPPING_INFORMATION_COOKIE_NAME = 'line_information';
    const LINE_INFO_LIST = [
        'utm_campaign',
        'utm_source',
        'utm_medium'
    ];

    /**
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface  $cookieManager,
        CookieMetadataFactory   $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    )
    {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param $cookieName
     * @return string|null
     */
    public function getCookie($cookieName)
    {
        return $this->cookieManager->getCookie($cookieName);
    }

    /**
     * @param $cookieName
     * @param $cookieValue
     * @param int $duration
     * @return bool
     */
    public function setCookie($cookieName, $cookieValue, int $duration = 24)
    {
        return setcookie(
            $cookieName,
            $cookieValue,
            [
                'expires' => ($duration * 3600) + time(),
                'path' => $this->sessionManager->getCookiePath(),
                'domain' => $this->sessionManager->getCookieDomain(),
            ]
        );
    }
}
