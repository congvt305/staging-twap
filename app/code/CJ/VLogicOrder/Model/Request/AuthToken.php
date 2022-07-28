<?php

namespace CJ\VLogicOrder\Model\Request;

use CJ\VLogicOrder\Helper\Data as VLogicHelper;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\VLogicOrder\Model\ResourceModel\Token\CollectionFactory;
use CJ\VLogicOrder\Model\TokenDataFactory;
use CJ\VLogicOrder\Logger\Logger as VLogicOrderLogger;

class AuthToken
{

    /**
     * @var Json
     */
    private $json;
    /**
     * @var VLogicHelper
     */
    private $vlogicHelper;

    /**
     * @var CollectionFactory
     */
    protected $tokenCollectionFactory;

    /**
     * @var TokenDataFactory
     */
    protected $tokenDataFactory;

    /**
     * @var VLogicOrderLogger
     */
    protected $logger;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @param ClientFactory $clientFactory
     * @param VLogicOrderLogger $logger
     * @param Json $json
     * @param VLogicHelper $vlogicHelper
     * @param TokenDataFactory $tokenDataFactory
     * @param CollectionFactory $tokenCollectionFactory
     */
    public function __construct(
        ClientFactory     $clientFactory,
        VLogicOrderLogger $logger,
        Json              $json,
        VLogicHelper      $vlogicHelper,
        TokenDataFactory  $tokenDataFactory,
        CollectionFactory $tokenCollectionFactory
    ){
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->json = $json;
        $this->vlogicHelper = $vlogicHelper;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->tokenDataFactory = $tokenDataFactory;
    }

    /**
     * @param $storeId
     * @param $format
     * @param $websiteId
     * @return array|bool|float|int|mixed|\Psr\Http\Message\ResponseInterface|string|null
     */
    public function requestAuthToken($storeId, $format = 'json', $websiteId = null)
    {
        $url = $this->vlogicHelper->getVLogicHost();
        $postData = [
            "StorerCode" => $this->vlogicHelper->getVLogicStorerCode($websiteId),
            "Username" => $this->vlogicHelper->getVLogicUsername($websiteId),
            "Password" => $this->vlogicHelper->getVLogicCPassword($websiteId)
        ];

        $client = $this->clientFactory->create();
        try {
            $response = $client->request(
                'POST',
                $url,
                [
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $postData
                ]
            );
        } catch (GuzzleException $exception) {
            $this->logger->info('Error when get access token: ' . $exception->getMessage());
        }
        if ($format == 'array') {
            $response = $this->json->unserialize($response->getBody()->getContents());
            if (isset($response['Token']) && $response['Token']) {
                try {
                    $token = $this->tokenDataFactory->create();
                    $token->setData([
                        'token' => $response['Token'],
                        'status' => 1,
                        'store_id' => $storeId
                    ]);
                    $token->save();
                } catch (\Exception $exception) {
                    $this->logger->info('Error when save access token: ' . $exception->getMessage());
                }
            }
        }
        return $response;
    }

    /**
     * @param $storeId
     * @return \Magento\Framework\DataObject|null
     */
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
