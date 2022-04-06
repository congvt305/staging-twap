<?php declare(strict_types=1);

namespace CJ\NinjaVanShipping\Model\Request;

use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Model\Request\AuthToken as NinjaVanToken;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\Serializer\Json;

class CancelShipment
{
    /**
     * @var NinjaVanToken
     */
    private NinjaVanToken $authToken;
    /**
     * @var NinjaVanHelper
     */
    private NinjaVanHelper $ninjavanHelper;
    /**
     * @var Json
     */
    private Json $json;

    private $numberOfRetry = 0;

    public function __construct(
        NinjaVanToken $authToken,
        NinjaVanHelper $ninjavanHelper,
        Json $json
    )
    {
        $this->authToken = $authToken;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->json = $json;
    }

    /**
     * @param string $trackingId
     * @param $order
     * @return array|bool|float|int|mixed|string|null
     * @throws \Exception
     */
    public function requestCancelShipment(string $trackingId = '', $order)
    {
        $tokenData = $this->authToken->getToken($order->getStoreId());
        $token = '';
        if (!$tokenData->getToken()) {
            $auth = $this->authToken->requestAuthToken('array', $order->getStoreId());
            if (isset($auth['access_token']) && $auth['access_token']) {
                $token = $auth['access_token'];
            } else {
                return $auth;
            }
        }
        $contents = [];

        $host = $this->ninjavanHelper->getNinjaVanHost();
        $hostLive = $this->ninjavanHelper->getNinjaVanHostLive();
        $countryCode = $this->ninjavanHelper->getNinjaVanCountryCode();
        $uri = $this->ninjavanHelper->getNinjaVanUriCancelOrder();
        if ($trackingId && $token) {
            $sandbox = (bool)$this->ninjavanHelper->isNinjaVanSandboxModeEnabled();
            if ($sandbox === false) {
                $url = $hostLive . strtoupper($countryCode) . $uri;
            } else {
                $url = $host . 'SG' . $uri;
            }
            $url .= $trackingId;
            $headers = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'verify' => false,
                'timeout' => 60,
                'http_errors' => false,
            ];
            $client = new \GuzzleHttp\Client($headers);
            $response = $client->delete($url);
            $contents = $this->json->unserialize($response->getBody()->getContents());
            if (isset($contents['code']) && $contents['code'] == 401) {
                $tokenData->setStatus(0)->save();
                $this->numberOfRetry++;
                $numOfRetry = $this->ninjavanHelper->getNinjaVanNumberRetry() ?? 4;
                if ($this->numberOfRetry == $numOfRetry) {
                    throw new \Exception('Something went wrong while connecting to NinjaVan.');
                }
                $this->requestCancelShipment($trackingId, $order);
            }
            if (isset($contents['messages'])) {
                throw new \Exception(implode (' | ', $contents['messages']));
            }
        }

        if (!$contents) {
            throw new \Exception('Cannot get access token from NinjaVan.');
        }

        return $contents;
    }
}
