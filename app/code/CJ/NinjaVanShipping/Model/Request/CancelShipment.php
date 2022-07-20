<?php declare(strict_types=1);

namespace CJ\NinjaVanShipping\Model\Request;

use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Model\Request\AuthToken as NinjaVanToken;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;

class CancelShipment
{
    const INVALID_TOKEN_CODE = 2005;
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
    /**
     * @var NinjaVanShippingLogger
     */
    private NinjaVanShippingLogger $logger;

    private $numberOfRetry = 0;

    public function __construct(
        NinjaVanToken $authToken,
        NinjaVanHelper $ninjavanHelper,
        Json $json,
        NinjaVanShippingLogger $logger
    )
    {
        $this->authToken = $authToken;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param string $trackingId
     * @param $order
     * @return array|bool|float|int|mixed|string|null
     * @throws \Exception
     */
    public function requestCancelShipment(string $trackingId = '', $order)
    {
        $storeId = $order->getStoreId();
        $tokenData = $this->authToken->getToken($storeId);
        $token = '';
        if (!$tokenData || !$tokenData->getToken()) {
            $auth = $this->authToken->requestAuthToken('array', $storeId);
            if (isset($auth['access_token']) && $auth['access_token']) {
                $token = $auth['access_token'];
            } else {
                return $auth;
            }
        }else{
            $token = $tokenData->getToken();
        }
        $contents = [];

        $host = $this->ninjavanHelper->getNinjaVanHost($storeId);
        $hostLive = $this->ninjavanHelper->getNinjaVanHostLive($storeId);
        $countryCode = $this->ninjavanHelper->getNinjaVanCountryCode($storeId);
        $uri = $this->ninjavanHelper->getNinjaVanUriCancelOrder($storeId);
        if ($trackingId && $token) {
            $sandbox = (bool)$this->ninjavanHelper->isNinjaVanSandboxModeEnabled($order->getStoreId());
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
            $jsonContents = $response->getBody()->getContents();

            $this->logger->info('Response from Cancel Ninja Van shipment api:');
            $this->logger->info($jsonContents);
            $jsonContents = $this->replaceErrorResponseSyntax($jsonContents);

            $contents = $this->json->unserialize($jsonContents);

            if (isset($contents['code'])
                && $response->getStatusCode() == 401
                && $contents['code'] == self::INVALID_TOKEN_CODE
            ) {
                try {
                    if ($tokenData && $tokenData->getDataByKey('token_id')) {
                        $tokenData->setStatus(0)->save();
                    }
                } catch (\Exception $exception) {
                    $this->logger->addError('Error when disable access token: ' . $exception->getMessage());
                }

                $numOfRetry = $this->ninjavanHelper->getNinjaVanNumberRetry() ?? 4;
                while ($this->numberOfRetry < $numOfRetry) {
                    $this->numberOfRetry++;
                    $this->logger->info(__("Retry to make cancel Order request {$this->numberOfRetry} time(s)"));

                    $auth = $this->authToken->requestAuthToken('array', $order->getStoreId());
                    $this->logger->addInfo('retry to get request token: ', $auth);
                    if (empty($auth['access_token'])) {
                        throw new \Exception('Cannot get access token from NinjaVan.');
                    }

                    $headers[RequestOptions::HEADERS] = ['Authorization' => 'Bearer ' . $auth['access_token']];
                    $this->logger->info('ninjavan | request header: ' . $this->json->serialize($headers));

                    $client = new \GuzzleHttp\Client($headers);
                    $response = $client->delete($url);
                    $jsonContents = $response->getBody()->getContents();

                    $this->logger->info('Response from resending Cancel Ninja Van shipment api:');
                    $this->logger->info($jsonContents);
                    $jsonContents = $this->replaceErrorResponseSyntax($jsonContents);
                    $contentsRetry = $this->json->unserialize($jsonContents);

                    if (isset($contentsRetry['code']) && $contentsRetry['code'] == self::INVALID_TOKEN_CODE) {
                        $this->logger->addError('Error when retrying to cancel NV Order: ' . $this->json->serialize($contentsRetry['error']));
                        continue;
                    }
                    $contents = $contentsRetry;
                    break;
                }
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

    /**
     * Replace json response with null value
     *
     * @param string $jsonContents
     * @return string
     */
    private function replaceErrorResponseSyntax(string $jsonContents): string
    {
        if (!empty($jsonContents) && strpos(':null', $jsonContents) !== false) {
            $jsonContents = str_replace(':null', ':""', $jsonContents);
        }
        return $jsonContents;
    }
}
