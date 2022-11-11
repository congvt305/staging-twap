<?php

namespace CJ\LineShopping\Plugin;

use CJ\LineShopping\Helper\CookieLineInformation;
use CJ\LineShopping\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Exception;
use CJ\LineShopping\Logger\Logger;
use Magento\Framework\App\RequestInterface;

class AddLineInfoToCookiePlugin
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
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseHttp $response
     * @return ResultInterface
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseHttp $response)
    {
        $this->setLineCookie();
        return $result;
    }

    /**
     * @return void
     */
    public function setLineCookie()
    {
        try {
            $params = $this->request->getParams();
            $duration = $this->config->getCookieLifeTime() ? $this->config->getCookieLifeTime() : CookieLineInformation::COOKIE_LIFETIME;

            if ($this->config->isEnableSaveUtm()) {
                $dataLine = [];
                foreach (CookieLineInformation::UTM_INFO_LIST as $item) {
                    if(isset($params[$item])) {
                        $dataLine[$item] = $params[$item];
                    }
                }

                if ($dataLine) {
                    $dataLine = $this->json->serialize($dataLine);
                    $this->cookieLineInformation->setCookie(CookieLineInformation::UTM_INFORMATION_COOKIE_NAME, $dataLine, $duration);
                }
            }

            if ($this->config->isEnable() && isset($params['ecid'])) {
                $this->cookieLineInformation->setCookie(CookieLineInformation::LINE_SHOPPING_ECID_COOKIE_NAME, $params['ecid'] , $duration);
                $this->logger->info(Logger::LINE_COOKIE,
                    [
                        'ecidCookie' => $params['ecid'],
                        'dataInfoCookie' => $dataLine
                    ]
                );
            }
        } catch (Exception $exception) {
            $this->logger->error(Logger::LINE_COOKIE,
                [
                    'ecidCookie' => $params['ecid'],
                    'message' => $exception->$exception()
                ]
            );
        }
    }
}
