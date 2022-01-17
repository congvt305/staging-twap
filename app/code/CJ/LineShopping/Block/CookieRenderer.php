<?php

namespace CJ\LineShopping\Block;

use CJ\LineShopping\Cookie\LineInformation as CookieLineInformation;
use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Cookie\LineInformation;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Exception;
use CJ\LineShopping\Logger\Logger;

class CookieRenderer extends Template
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
     * @param Logger $logger
     * @param LineInformation $cookieLineInformation
     * @param Config $config
     * @param Json $json
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Logger $logger,
        CookieLineInformation $cookieLineInformation,
        Config $config,
        Json $json,
        Template\Context $context,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->cookieLineInformation = $cookieLineInformation;
        $this->config = $config;
        $this->json = $json;
        parent::__construct($context, $data);
        $this->setLineCookie();
    }

    /**
     * @return void
     */
    public function setLineCookie()
    {
        try {
            $params = $this->getRequest()->getParams();
            if($this->config->isEnable() && isset($params['ecid'])) {
                $duration = $this->config->getCookieLifeTime();
                $this->cookieLineInformation->setCookie(CookieLineInformation::LINE_SHOPPING_ECID_COOKIE_NAME, $params['ecid'] , $duration);
                $dataLine = [];
                foreach (CookieLineInformation::LINE_INFO_LIST as $item) {
                    if(isset($params[$item])) {
                        $dataLine[$item] = $params[$item];
                    }
                }
                if($dataLine) {
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
