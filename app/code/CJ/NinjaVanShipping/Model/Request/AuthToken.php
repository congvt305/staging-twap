<?php

namespace CJ\NinjaVanShipping\Model\Request;

use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\NinjaVanShipping\Model\ResourceModel\Token\CollectionFactory;
use CJ\NinjaVanShipping\Model\TokenDataFactory;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;

class AuthToken
{
    const COUNTRY = 'my';

    /**
     * @var Json
     */
    private $json;
    /**
     * @var NinjaVanHelper
     */
    private $ninjavanHelper;

    /**
     * @var CollectionFactory
     */
    protected $tokenCollectionFactory;

    /**
     * @var TokenDataFactory
     */
    protected $tokenDataFactory;

    /**
     * @var NinjaVanShippingLogger
     */
    protected $logger;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @param ClientFactory $clientFactory
     * @param NinjaVanShippingLogger $logger
     * @param Json $json
     * @param NinjaVanHelper $ninjavanHelper
     * @param TokenDataFactory $tokenDataFactory
     * @param CollectionFactory $tokenCollectionFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        NinjaVanShippingLogger $logger,
        Json $json,
        NinjaVanHelper $ninjavanHelper,
        TokenDataFactory $tokenDataFactory,
        CollectionFactory $tokenCollectionFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->json = $json;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->tokenDataFactory = $tokenDataFactory;
    }

    public function requestAuthToken($format, $storeId)
    {
        if (empty($format)) {
            $format = 'json';
        }
        $sandbox = (bool)$this->ninjavanHelper->isNinjaVanSandboxModeEnabled($storeId);
        if ($sandbox === false) {
            $countryCode = $this->ninjavanHelper->getNinjaVanCountryCode($storeId);
            $url = $this->ninjavanHelper->getNinjaVanHostLive($storeId);
        } else {
            $countryCode = 'sg';
            $url = $this->ninjavanHelper->getNinjaVanHost($storeId);
        }
        $uri = strtoupper($countryCode) . '/2.0/oauth/access_token';

        $url .= $uri;

        $postData = [
            "client_id" => $this->ninjavanHelper->getNinjaVanClientId($storeId),
            "client_secret" => $this->ninjavanHelper->getNinjaVanClientKey($storeId),
            "grant_type" => "client_credentials"
        ];
        $this->logger->info('ninjavan | request access token payload: ' . $this->json->serialize($postData));
        $client = $this->clientFactory->create();
        $response = [];
        try {
            $response = $client->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $postData
                ]
            );

            if ($format == 'array') {
                $response = $this->json->unserialize($response->getBody()->getContents());
                if (isset($response['access_token']) && $response['access_token']) {
                    try {
                        $token = $this->tokenDataFactory->create();
                        $token->setData([
                            'token' => $response['access_token'],
                            'status' => 1,
                            'store_id' => $storeId
                        ]);
                        $token->save();
                        sleep(1);
                    } catch (\Exception $exception) {
                        $this->logger->error('Error when save access token: ' . $exception->getMessage());
                    }
                }
            }
        } catch (GuzzleException $exception) {
            $this->logger->error('Error when get access token: ' . $exception->getMessage());
        }
        return $response;
    }

    public function getToken($storeId)
    {
        $collection = $this->tokenCollectionFactory->create()
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->setOrder('updated_at', 'DESC');

        $tokenData = $collection->getFirstItem();

        if ($tokenData && $tokenData->getToken()) {
            return $tokenData;
        }
        return null;
    }
}
