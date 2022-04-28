<?php

declare(strict_types=1);

namespace CJ\NinjaVanShipping\Model\Request;

use CJ\NinjaVanShipping\Api\GenerateWaybillInterface;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use CJ\NinjaVanShipping\Model\Request\AuthToken as NinjaVanToken;
use CJ\NinjaVanShipping\Model\TokenDataFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\RequestOptions;

class GenerateWaybill implements GenerateWaybillInterface
{
    const DEFAULT_RETRY_LIMIT = 4;

    /**
     * @var NinjaVanToken
     */
    private NinjaVanToken $authToken;

    /**
     * @var NinjaVanHelper
     */
    private NinjaVanHelper $ninjavanHelper;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var TokenDataFactory
     */
    private TokenDataFactory $tokenDataFactory;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var NinjaVanShippingLogger
     */
    private NinjaVanShippingLogger $logger;

    /**
     * @var int
     */
    private int $numberOfRetry = 0;

    /**
     * @param AuthToken $authToken
     * @param TokenDataFactory $tokenDataFactory
     * @param NinjaVanHelper $ninjavanHelper
     * @param Json $json
     * @param ClientFactory $clientFactory
     * @param NinjaVanShippingLogger $logger
     */
    public function __construct(
        NinjaVanToken $authToken,
        TokenDataFactory $tokenDataFactory,
        NinjaVanHelper $ninjavanHelper,
        Json $json,
        ClientFactory $clientFactory,
        NinjaVanShippingLogger $logger
    ) {
        $this->authToken = $authToken;
        $this->tokenDataFactory = $tokenDataFactory;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->clientFactory = $clientFactory;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param $trackingNumbers
     * @param $storeId
     * @return array|bool|float|int|mixed|\Psr\Http\Message\ResponseInterface|string|null
     * @throws LocalizedException
     */
    public function process($trackingNumbers, $storeId)
    {
        if (empty($trackingNumbers)) {
            throw new LocalizedException(__('Tracking number(s) are missing.'));
        }

        $storedToken = $this->authToken->getToken($storeId);
        if (!$storedToken || !$storedToken->getToken()) {
            $auth = $this->authToken->requestAuthToken('array', $storeId);
            if (empty($auth['access_token'])) {
                return $auth;
            }
            $token = $auth['access_token'];
        } else {
            $token = $storedToken->getToken();
        }

        if (empty($token)) {
            throw new LocalizedException(__('Cannot get access token from NinjaVan.'));
        }

        $uri = $this->ninjavanHelper->getNinjaVanUriGenerateWaybill();
        if (empty($uri)) {
            throw new LocalizedException(__('Generate Waybill Api URI hasn\'t been set.'));
        }

        $client = $this->clientFactory->create(['config' => $this->prepareConfigs($token)]);
        $queryParams = $this->prepareParams($trackingNumbers);
        $apiUri = $this->getBaseUrl() . $uri . $queryParams;

        $this->logger->info('Request body to generate NinjaVan waybill:');
        $this->logger->info($this->json->serialize(['config' => $client->getConfig(), 'uri' => $apiUri]));

        $response = $client->get($apiUri);
        $contents = $response->getBody()->getContents();
        if ($response->getStatusCode() === 200) {
            return $contents;
        }

        $this->logger->info('Error response from generate NinjaVan waybill\'s api:');
        $this->logger->info($contents);

        $contents = $this->json->unserialize($contents);
        if (isset($contents['error'])
            && ($response->getStatusCode() == 401
                || (isset($contents['error']['code']) && $contents['error']['code'] == 2001))
        ) {
            $this->disableExpiredToken($storedToken);
            $this->numberOfRetry++;
            $numOfRetry = $this->ninjavanHelper->getNinjaVanNumberRetry() ?? self::DEFAULT_RETRY_LIMIT;
            if ($this->numberOfRetry >= $numOfRetry) {
                throw new LocalizedException(__('Something went wrong while connecting to NinjaVan.'));
            }
            return $this->process($trackingNumbers, $storeId);
        }

        if (isset($contents['messages'])) {
            throw new LocalizedException(__(implode (' | ', $contents['messages'])));
        }

        return $contents;
    }

    /**
     * @param string $token
     * @return array
     */
    protected function prepareConfigs(string $token): array
    {
        return [
            RequestOptions::HEADERS => ['Authorization' => "Bearer {$token}"],
            RequestOptions::VERIFY => false,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => 60
        ];
    }

    /**
     * @param string|array $trackingNumbers
     * @return string
     */
    protected function prepareParams($trackingNumbers): string
    {
        if (is_array($trackingNumbers)) {
            $trackingNumbers = implode(',', $trackingNumbers);
        }

        return '?' . http_build_query([
            'tids' => $trackingNumbers,
            'h' => 0 // 0: Show shipper's detail, 1: Hide shipper's detail
        ]);
    }

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $countryCode = $this->ninjavanHelper->getNinjaVanCountryCode();
        $isSandboxMode = (bool)$this->ninjavanHelper->isNinjaVanSandboxModeEnabled();
        if ($isSandboxMode) {
            return $this->ninjavanHelper->getNinjaVanHost() . 'SG';
        }
        return $this->ninjavanHelper->getNinjaVanHostLive() . strtoupper($countryCode);
    }

    /**
     * @param $storedToken
     * @return void
     */
    private function disableExpiredToken($storedToken): void
    {
        try {
            if ($storedToken && $tokenId = $storedToken->getDataByKey('token_id')) {
                $tokenDataFactory = $this->tokenDataFactory->create()->load($tokenId);
                $tokenDataFactory->setStatus(0)->save();
            }
        } catch (\Exception $exception) {
            $this->logger->addError('Error when disabling access token: ' . $exception->getMessage());
        }
    }
}
