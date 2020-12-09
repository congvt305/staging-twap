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
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Request constructor.
     * @param Curl $curl
     * @param Json $json
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $config,
        LoggerInterface $logger
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

        if (!empty($url) && $active) {
            try {
                $this->curl->addHeader('Content-Type', 'application/json');

                if ($this->config->getSSLVerification()) {
                    $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
                    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
                }

                $this->curl->post($url, $this->json->serialize($requestData));

                $response = $this->curl->getBody();

                return $this->json->unserialize($response);
            } catch (PosPointsException $exception) {
                $this->logger->error($exception->getMessage());
                return [];
            }
        } else {
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
