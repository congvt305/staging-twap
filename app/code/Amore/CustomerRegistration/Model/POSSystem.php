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
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amore\CustomerRegistration\Model\Sequence;

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
    private $confg;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Amore\CustomerRegistration\Model\Sequence
     */
    private $sequence;
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

    public function __construct(
        Curl $curl,
        Data $confg,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        DateTime $date,
        Sequence $sequence,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Zend\Http\Client $zendClient
    ) {
        $this->date = $date;
        $this->confg = $confg;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->sequence = $sequence;
        $this->curlClient = $curl;
        $this->httpClientFactory = $httpClientFactory;
        $this->zendClient = $zendClient;
    }

    public function getMemberInfo($firstName, $lastName, $mobileNumber)
    {
        $posData = $this->callPOSInfoAPI($firstName, $lastName, $mobileNumber);
        if(isset($posData['birthDay'])) {
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 4, 0);
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 7, 0);
        }
        return $posData;
    }

    private function callPOSInfoAPI($firstName, $lastName, $mobileNumber)
    {
        $result = [];
        try {
            $url = $this->confg->getMemberInfoURL();

            $parameters = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobileNumber' => $mobileNumber,
            ];
            $jsonEncodedData = json_encode($parameters);

            $this->curlClient->setOptions(array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonEncodedData,
                CURLOPT_HTTPHEADER => array(
                    'Content-type: application/json'
                ),
            ));
            $this->curlClient->post($url, $parameters);
            $apiRespone = $this->curlClient->getBody();
            $response = json_decode($apiRespone, true);
            if ($response['message'] == 'SUCCESS') {
                $result = $response['data']['customerInfo'];
            } else {
                $result['message'] = $response['message'];
            }

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;

    }

    /**
     * It will call the POS API join
     * Whenever customer will update or register this function will call and it will sync with the POS system
     *
     * @param Customer $customer
     * @param string   $action
     */
    public function syncMember($customer, $action)
    {
        try {
            $customer = $this->assignIntegrationNumber($customer);
            $parameters = [];
            $parameters['cstmIntgSeq'] = $customer->getCustomAttribute('integration_number');
            $parameters['if_flag'] = 'I';
            $parameters['firstName'] = $customer->getFirstname();
            $parameters['lastName'] = $customer->getLastname();
            $parameters['birthDay'] = $customer->getDob();
            $parameters['mobileNo'] = $customer->getCustomAttribute('mobile_number');
            $parameters['email'] = $customer->getEmail();
            $parameters['sex'] = $customer->getGender() == '1' ? 'M' : 'F';
            $parameters['emailYN'] = $this->isCustomerSubscribToNewsLetters($customer->getId()) ? 'Y' : 'N';
            $parameters['smsYN'] = $customer->getCustomAttribute('sms_subscription_status')->getValue() == 1 ? 'Y' : 'N';
            $parameters['callYN'] = 'N';
            $parameters['dmYN'] = $customer->getCustomAttribute('dm_subscription_status')->getValue() == 1 ? 'Y' : 'N';
            $parameters['homeCity'] = $customer->getCustomAttribute('dm_city');
            $parameters['homeState'] = $customer->getCustomAttribute('dm_state');
            $parameters['homeAddr1'] = $customer->getCustomAttribute('dm_detailed_address');
            $parameters['homeZip'] = $customer->getCustomAttribute('dm_zipcode');
            $parameters['statusCD'] = $action == 'register' ? '01' : '02';

            $response = $this->callJoinAPI($parameters);
            $this->savePOSSyncReport($customer, $response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function assignIntegrationNumber($customer)
    {
        try {
            $posOrOnline = 'online';
            if ($customer->getCustomAttribute('referrer_code')) {
                $posOrOnline = 'pos';
            }
            if ($posOrOnline == 'online') {
                $posOrOnline = $customer->getCustomAttribute('imported_from_pos')->getValue() == 1 ? 'pos' : 'online';
            }

            $this->sequence->setCustomerType($posOrOnline);
            $secquenceNumber = $this->sequence->getNextValue();
            $customer->setCustomAttribute('integration_number', $secquenceNumber);
            return $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $e->getMessage();
            return $customer;
        }
    }

    private function callJoinAPI($parameters)
    {
        return true;

        $response = $this->doRequest(
            $this->confg->getMemberJoinURL(),
            $parameters,
            Request::HTTP_METHOD_POST
        );

        return $response;
        /** @var ZendClient $client */
       /* $client = $this->httpClientFactory->create();
        $client->setUri($this->confg->getMemberJoinURL());
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept', 'application/json');
        $client->setHeaders("Authorization", "Bearer yourvalue");
        $client->setParameterPost($parameters);

        try {
            $response = $client->request();

            $responseArray = [];
            parse_str(strstr($response->getBody(), 'RESULT'), $responseArray);

            $result->setData(array_change_key_case($responseArray, CASE_LOWER));
            $result->setData('result_code', $result->getData('result'));
        } catch (\Zend_Http_Client_Exception $e) {
            return $e->getMessage();
        }

        return $result;*/
    }

    /**
     * To check whether customer is subscribed to the news letters or not
     *
     * @param int $customerId
     *
     * @return bool
     */
    private function isCustomerSubscribToNewsLetters($customerId) {
        /**
         * @var Subscriber $subscriber
         */
        $subscriber = $this->subscriberFactory->create();
        $status = $subscriber->loadByCustomerId((int)$customerId)->isSubscribed();

        return (bool)$status;
    }

    /**
     * To save the POS API response with the customer
     *
     * @param Customer $customer
     * @param $syncResult
     */
    private function savePOSSyncReport($customer, $syncResult)
    {
        $customer->setCustomAttribute('pos_synced_report', 'Done');
        $customer->setCustomAttribute('pos_synced_successfully', 1);
        $this->customerRepository->save($customer);
    }

    /**
     * Do API request with provided params.
     * I follow the way recommended at https://devdocs.magento.com/guides/v2.4/ext-best-practices/tutorials/create-integration-with-api.html
     * to call external APIs
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
