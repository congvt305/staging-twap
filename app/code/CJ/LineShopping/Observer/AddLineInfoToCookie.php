<?php

namespace CJ\LineShopping\Observer;

use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Helper\CookieLineInformation;
use CJ\LineShopping\Logger\Logger;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AddLineInfoToCookie implements ObserverInterface
{
    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var CookieLineInformation
     */
    protected CookieLineInformation $cookieLineInformation;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param RequestInterface $request
     * @param Logger $logger
     * @param CookieLineInformation $cookieLineInformation
     * @param Config $config
     * @param Json $json
     */
    public function __construct(
        RequestInterface $request,
        Logger $logger,
        CookieLineInformation $cookieLineInformation,
        Config $config,
        Json $json
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->cookieLineInformation = $cookieLineInformation;
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->setLineCookie();
    }

    /**
     * @return void
     */
    public function setLineCookie()
    {
        try {
            $params = $this->request->getParams();
            if ($this->config->isEnable() && isset($params['ecid'])) {
                $duration = $this->config->getCookieLifeTime();
                $this->cookieLineInformation->setCookie(CookieLineInformation::LINE_SHOPPING_ECID_COOKIE_NAME, $params['ecid'] , $duration);
                $dataLine = [];
                foreach (CookieLineInformation::LINE_INFO_LIST as $item) {
                    if(isset($params[$item])) {
                        $dataLine[$item] = $params[$item];
                    }
                }
                if ($dataLine) {
                    $dataLine = $this->json->serialize($dataLine);
                    $this->cookieLineInformation->setCookie(CookieLineInformation::LINE_SHOPPING_INFORMATION_COOKIE_NAME, $dataLine, $duration);
                }
                $this->logger->addInfo(Logger::LINE_COOKIE,
                    [
                        'ecidCookie' => $params['ecid'],
                        'dataInfoCookie' => $dataLine
                    ]
                );
            }
        } catch (Exception $exception) {
            $this->logger->addError(Logger::LINE_COOKIE,
                [
                    'ecidCookie' => $params['ecid'],
                    'message' => $exception->$exception()
                ]
            );
        }
    }
}
