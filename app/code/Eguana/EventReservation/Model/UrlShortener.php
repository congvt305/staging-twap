<?php

namespace Eguana\EventReservation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use \Magento\Store\Model\ScopeInterface as Scope;

class UrlShortener
{

    const ACCESS_TOKEN_CONFIGURATION_PATH = 'event_reservation/shortener/access_token';
    const API_ENDPOINT_CONFIGURATION_PATH = 'event_reservation/shortener/api_endpoint';

    /**
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param ScopeConfig $scopeConfig
     * @param Curl $curl
     * @param Json $json
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Curl                 $curl,
        Json $json
    ){
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    private function getAccessToken($websiteId)
    {
        return $this->scopeConfig->getValue(self::ACCESS_TOKEN_CONFIGURATION_PATH, Scope::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    private function getApiEndpoint($websiteId)
    {
        return $this->scopeConfig->getValue(self::API_ENDPOINT_CONFIGURATION_PATH, Scope::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $url
     * @param $websiteId
     * @return mixed
     */
    public function shortenUrl($url, $websiteId)
    {
        $accessToken = $this->getAccessToken($websiteId);
        $apiEndpoint = $this->getApiEndpoint($websiteId);
        if (!$accessToken || !$apiEndpoint || !$url) {
            return $url;
        }
        $headers = [
            "Authorization" => "Bearer {$accessToken}",
            "Content-Type" => "application/json"
        ];
        $requestBody = ['long_url' => $url];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HEADER, false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->curl->setHeaders($headers);
        $this->curl->post($apiEndpoint, $this->json->serialize($requestBody));
        $response = $this->json->unserialize($this->curl->getBody());
        return $response['link'] ?? $url;
    }
}
