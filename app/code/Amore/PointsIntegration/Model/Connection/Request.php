<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:38
 */

namespace Amore\PointsIntegration\Model\Connection;

use Amore\PointsIntegration\Exception\PosPointsException;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Amore\PointsIntegration\Logger\Logger;

class Request
{
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Request constructor.
     * @param Curl $curl
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $config,
        Logger $logger
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param $requestData
     * @param $websiteId
     * @param $type
     * @return array|bool|float|int|mixed|string|null
     */
    public function sendRequest($requestData, $websiteId, $type)
    {
        $url = $this->getUrl($type, $websiteId);
        $active = $this->config->getActive($websiteId);

        if ($this->config->getLoggerActiveCheck($websiteId)) {
            $this->logger->info("BEFORE SEND REQUEST");
            $this->logger->debug($requestData);
        }

        if ($this->config->getLoggerActiveCheck($websiteId)) {
            $this->logger->info("========== REQUEST ==========");
            $this->logger->info($this->json->serialize($requestData));
        }

        if (!empty($url) && $active) {
            try {
                $this->curl->addHeader('Content-Type', 'application/json');
//
//                if ($this->config->getSSLVerification()) {
//                    $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
//                    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
//                }
//
//                $this->curl->post($url, $this->json->serialize($requestData));

//                $response = $this->curl->getBody();


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_URL, $url);
                // post_data
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                $response = curl_exec($ch);
                curl_close($ch);


                if ($this->config->getLoggerActiveCheck($websiteId)) {
                    $this->logger->info("========== RESPONSE ==========");
                    $this->logger->info($response);
                }

                return $this->json->unserialize($response);
            } catch (PosPointsException $exception) {
                $this->logger->error($exception->getMessage());
                return [];
            }
        } else {
            $this->logger->info("URL IS EMPTY OR MODULE INACTIVE");
            return [];
        }
    }

    public function getUrl($type, $websiteId)
    {
        $path = '';
        switch ($type) {
            case 'memberSearch':
                $path = $this->config->getMemberSearchURL($websiteId);
                break;
            case 'redeemSearch':
                $path = $this->config->getRedeemSearchURL($websiteId);
                break;
            case 'pointSearch':
                $path = $this->config->getPointSearchURL($websiteId);
                break;
            case 'customerOrder':
                $path = $this->config->getCustomerOrderURL($websiteId);
                break;
        }
        return $path;
    }
}
