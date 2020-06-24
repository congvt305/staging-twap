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
use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Framework\Serialize\Serializer\Json;

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
    /**
     * @var POSLogger
     */
    private $logger;
    /**
     * @var Json
     */
    private $json;

    public function __construct(
        Curl $curl,
        Data $confg,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        DateTime $date,
        Sequence $sequence,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Zend\Http\Client $zendClient,
        POSLogger $logger,
        Json $json
    ) {
        $this->date = $date;
        $this->confg = $confg;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->sequence = $sequence;
        $this->curlClient = $curl;
        $this->httpClientFactory = $httpClientFactory;
        $this->zendClient = $zendClient;
        $this->logger = $logger;
        $this->json = $json;
    }

    public function getMemberInfo($firstName, $lastName, $mobileNumber)
    {
        $posData = $this->callPOSInfoAPI($firstName, $lastName, $mobileNumber);
        if (isset($posData['birthDay'])) {
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 4, 0);
            $posData['birthDay'] = substr_replace($posData['birthDay'], '/', 7, 0);
        }
        return $posData;
    }

    private function callPOSInfoAPI($firstName, $lastName, $mobileNumber)
    {
        $result = [];
        $url = $this->confg->getMemberInfoURL();
        try {
            $parameters = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobileNumber' => $mobileNumber,
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
            $this->logger->addAPICallLog(
                'POS get info API Call',
                $url,
                $parameters
            );
            $this->curlClient->post($url, $parameters);
            $apiRespone = $this->curlClient->getBody();
            $response = $this->json->unserialize($apiRespone);
            if ($response['message'] == 'SUCCESS') {
                $result = $response['data']['customerInfo'];
            } else {
                $result['message'] = $response['message'];
            }
            $this->logger->addAPICallLog(
                'POS get info API Response',
                $url,
                $response
            );
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $this->logger->addExceptionMessage($e->getMessage());
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
            $parameters['cstmIntgSeq'] = $customer->getCustomAttribute('integration_number')->getValue();
            $parameters['if_flag'] = 'I';
            $parameters['firstName'] = $customer->getFirstname();
            $parameters['lastName'] = $customer->getLastname();
            $parameters['birthDay'] = $customer->getDob()?$customer->getDob():'';
            $parameters['mobileNo'] = $customer->getCustomAttribute('mobile_number')->getValue();
            $parameters['email'] = $customer->getEmail();
            $parameters['sex'] = $customer->getGender() == '1' ? 'M' : 'F';
            $parameters['emailYN'] = $this->isCustomerSubscribToNewsLetters($customer->getId()) ? 'Y' : 'N';
            $parameters['smsYN'] = $customer->getCustomAttribute('sms_subscription_status')->getValue() == 1 ? 'Y':'N';
            $parameters['callYN'] = 'N';
            $parameters['dmYN'] = $customer->getCustomAttribute('dm_subscription_status')->getValue() == 1 ? 'Y' : 'N';
            $parameters['homeCity'] = $customer->getCustomAttribute('dm_city')?
                $customer->getCustomAttribute('dm_city')->getValue():'';
            $parameters['homeState'] = $customer->getCustomAttribute('dm_state')?
                $customer->getCustomAttribute('dm_state')->getValue():'';
            $parameters['homeAddr1'] = $customer->getCustomAttribute('dm_detailed_address')?
                $customer->getCustomAttribute('dm_detailed_address')->getValue():'';
            $parameters['homeZip'] = $customer->getCustomAttribute('dm_zipcode')?
                $customer->getCustomAttribute('dm_zipcode')->getValue():'';
            $parameters['salOrgCd'] =  $customer->getCustomAttribute('sales_organization_code')?
                $customer->getCustomAttribute('sales_organization_code')->getValue():'';
            $parameters['salOffCd'] = $customer->getCustomAttribute('sales_office_code')?
                $customer->getCustomAttribute('sales_office_code')->getValue():'';
            $parameters['statusCD'] = $action == 'register' ? '01' : '02';

            $response = $this->callJoinAPI($parameters);
            $this->savePOSSyncReport($customer, $response);
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
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
            $customer->setCustomAttribute('sales_organization_code', $this->confg->getOrganizationSalesCode());
            $customer->setCustomAttribute('sales_office_code', $this->confg->getOfficeSalesCode());
            return $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $e->getMessage();
            $this->logger->addExceptionMessage($e->getMessage());
            return $customer;
        }
    }

    private function callJoinAPI($parameters)
    {
        $result = [];
        try {
            $url = $this->confg->getMemberJoinURL();

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
        }

        return $result;
    }

    /**
     * To check whether customer is subscribed to the news letters or not
     *
     * @param int $customerId
     *
     * @return bool
     */
    private function isCustomerSubscribToNewsLetters($customerId)
    {
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
        $customer->setCustomAttribute('pos_synced_report', $syncResult['message']);
        $customer->setCustomAttribute('pos_synced_successfully', $syncResult['status']);
        $this->customerRepository->save($customer);
    }

}
