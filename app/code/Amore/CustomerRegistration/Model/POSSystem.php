<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 8
 * Time: 오후 1:17
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Model\Data\Customer;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;

/**
 * In this class we will call the POS API
 * Class POSSystem
 * @package Amore\CustomerRegistration\Model
 */
class POSSystem
{

    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var Data
     */
    private $config;

    /**
     * @var Curl
     */
    private $curlClient;
    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var \Zend\Http\Client
     */
    private $zendClient;
    /**
     * @var POSLogger
     */
    private $logger;
    /**
     * @var Json
     */
    private $json;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var RegionResourceModel
     */
    private $regionResourceModel;
    /**
     * @var \Eguana\Directory\Helper
     */
    private $cityHelper;

    public function __construct(
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        \Eguana\Directory\Helper\Data $cityHelper,
        Curl $curl,
        Data $config,
        DateTime $date,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Zend\Http\Client $zendClient,
        POSLogger $logger,
        Json $json,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->date = $date;
        $this->config = $config;
        $this->curlClient = $curl;
        $this->httpClientFactory = $httpClientFactory;
        $this->zendClient = $zendClient;
        $this->logger = $logger;
        $this->json = $json;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->cityHelper = $cityHelper;
    }

    public function getMemberInfo($firstName, $lastName, $mobileNumber)
    {
        $posData = $this->callPOSInfoAPI(trim($firstName), trim($lastName), trim($mobileNumber));
        if (isset($posData['birthDay'])) {
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 4, 0);
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 7, 0);
        }
        return $posData;
    }

    private function callPOSInfoAPI($firstName, $lastName, $mobileNumber)
    {
        $result = [];
        $response = [];
        $url = $this->config->getMemberInfoURL();
        $callSuccess = 1;
        try {
            $parameters = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobileNumber' => $mobileNumber,
                'salOrgCd' => $this->config->getOrganizationSalesCode(),
                'salOffCd' => $this->config->getOfficeSalesCode()
            ];
            $jsonEncodedData = json_encode($parameters);

            $this->curlClient->setOptions([
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonEncodedData,
                CURLOPT_HTTPHEADER => [
                    'Content-type: application/json'
                ],
            ]);

            if ($this->config->getSSLVerification()) {
                $this->curlClient->setOption(CURLOPT_SSL_VERIFYHOST, false);
                $this->curlClient->setOption(CURLOPT_SSL_VERIFYPEER, false);
            }

            $this->logger->addAPICallLog(
                'POS get info API Call',
                $url,
                $parameters
            );
            $this->curlClient->post($url, $parameters);
            $apiRespone = $this->curlClient->getBody();
            $response = $this->json->unserialize($apiRespone);
            if ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'Y') {
                if ($response['data']['checkCnt'] > 1) {
                    $result['message'] =  __(
                        'The requested membership information is already registered.'
                    );
                } elseif (isset($response['data']['customerInfo']['cstmIntgSeq']) == false ||
                    $response['data']['customerInfo']['cstmIntgSeq'] == ''
                ) {
                    $result['message'] =  __(
                        'There is no customer integration number from POS. Please contact to the admin.'
                    );
                } else {
                    $result = $response['data']['customerInfo'];
                    $result['region'] = [];
                    if ($result['homeCity']) {
                        /** @var \Magento\Directory\Model\Region $region */
                        $region = $this->regionFactory->create();
                        $this->regionResourceModel->load($region, $result['homeCity'], 'code');
                        $result['region'] = $region->getData();
                    }

                    if ($result['homeState'] && $result['region']['region_id']) {
                        $cities = $this->cityHelper->getCityData();
                        $regionCities = $cities[$result['region']['region_id']];
                        foreach ($regionCities as $regionCity) {
                            if ($regionCity['code'] == $result['homeState']) {
                                $result['city'] = $regionCity;
                                break;
                            }
                        }
                    }
                }
            } elseif ($response['message'] == 'SUCCESS' && $response['data']['checkYN'] == 'N') {
                $result = [];
            } else {
                $result['message'] = $response['message'];
            }
            $this->logger->addAPICallLog(
                'POS get info API Response',
                $url,
                $response
            );

        } catch (\Exception $e) {
            if ($e->getMessage() == '<url> malformed') {
                $result['message'] = __('Please first configure POS APIs properly. Then try again.');
            } else {
                $result['message'] = $e->getMessage();
            }
            $this->logger->addExceptionMessage($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteCode = $this->storeManager->getWebsite()->getCode();

        $this->eventManager->dispatch(
            'eguana_bizconnect_operation_processed',
            [
                'topic_name' => 'eguana.pos.get.info',
                'direction' => 'outgoing',
                'to' => $websiteCode, //from or to
                'serialized_data' => $this->json->serialize($log),
                'status' => $callSuccess,
                'result_message' => isset($result['message'])?$result['message']:'Fail'
            ]
        );

        return $result;
    }

    /**
     * It will call the POS API join
     * Whenever customer will update or register this function will call and it will sync with the POS system
     *
     * @param Array $parameters
     */
    public function syncMember($parameters)
    {
        try {
            $response = $this->callJoinAPI($parameters);
            //$this->savePOSSyncReport($parameters, $response);
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
            return $e->getMessage();
        }
    }

    private function callJoinAPI($parameters)
    {
        $result = [];
        $callSuccess = 1;
        $response = [];
        try {
            $url = $this->config->getMemberJoinURL();

            $jsonEncodedData = json_encode($parameters);

            $this->curlClient->setOptions([
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonEncodedData,
                CURLOPT_HTTPHEADER => [
                    'Content-type: application/json'
                ],
            ]);

            if ($this->config->getSSLVerification()) {
                $this->curlClient->setOption(CURLOPT_SSL_VERIFYHOST, false);
                $this->curlClient->setOption(CURLOPT_SSL_VERIFYPEER, false);
            }

            $this->logger->addAPICallLog(
                'POS set info API Call',
                $url,
                $parameters
            );
            $this->curlClient->post($url, $parameters);
            $apiRespone = $this->curlClient->getBody();
            $response = $this->json->unserialize($apiRespone);
            if ($response['message'] == 'SUCCESS') {
                $result['message'] = $response['message'];
                $result['status'] = 1;
            } else {
                $result['message'] = $response['message'];
                $result['status'] = 0;
            }
            $this->logger->addAPICallLog(
                'POS set info API Response',
                $url,
                $response
            );

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['status'] = 0;
            $this->logger->addExceptionMessage($e->getMessage());
            $callSuccess = 0;
        }

        $log['request'] = $parameters;
        $log['response'] = $response;

        $websiteCode = $this->storeManager->getWebsite()->getCode();

        $this->eventManager->dispatch(
            'eguana_bizconnect_operation_processed',
            [
                'topic_name' => 'eguana.pos.sync.info',
                'direction' => 'outgoing',
                'to' => $websiteCode, //from or to
                'serialized_data' => $this->json->serialize($log),
                'status' => $callSuccess,
                'result_message' => isset($result['message'])?$result['message']:'Fail'
            ]
        );

        return $result;
    }

    /**
     * To save the POS API response with the customer
     *
     * @param Array $parameters
     * @param $syncResult
     */
    private function savePOSSyncReport($parameters, $syncResult)
    {
        //$customer->setCustomAttribute('pos_synced_report', $syncResult['message']);
        //$customer->setCustomAttribute('pos_synced_successfully', $syncResult['status']);
        //$this->customerRepository->save($customer);
    }

}
