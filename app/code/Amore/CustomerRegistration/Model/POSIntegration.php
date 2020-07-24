<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 21
 * Time: 오후 5:37
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Store\Model\StoreRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Newsletter\Model\SubscriberFactory;
use Amore\CustomerRegistration\Model\POSLogger;
use Amore\CustomerRegistration\Api\Data\ResponseInterface;
use Amore\CustomerRegistration\Api\Data\DataResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;

/**
 * Implement the API module interface
 * Class POSIntegration
 */
class POSIntegration implements \Amore\CustomerRegistration\Api\POSIntegrationInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var Data
     */
    private $configHelper;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var \Amore\CustomerRegistration\Model\POSLogger
     */
    private $logger;

    /**
     * @var \Amore\CustomerRegistration\Api\Data\ResponseInterface
     */
    private $response;
    /**
     * @var DataResponseInterface
     */
    private $dataResponse;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var \Eguana\Directory\Helper\Data
     */
    private $cityHelper;
    /**
     * @var RegionResourceModel
     */
    private $regionResourceModel;

    public function __construct(
        RegionFactory $regionFactory,
        \Eguana\Directory\Helper\Data $cityHelper,
        RegionResourceModel $regionResourceModel,
        Data $configHelper,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreRepository $storeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger,
        ResponseInterface $response,
        DataResponseInterface $dataResponse,
        Json $json,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
        $this->response = $response;
        $this->dataResponse = $dataResponse;
        $this->json = $json;
        $this->eventManager = $eventManager;
        $this->regionFactory = $regionFactory;
        $this->cityHelper = $cityHelper;
        $this->regionResourceModel = $regionResourceModel;
    }

    /**
     * To Update a customer information.
     * In parameters I did not use the inteface becasue client can not change the parameters name.
     * Such as client want to send the parameter to set first name as firstName. So if I will
     * use the interface then I can get it as first first_name
     * or firstname. Thats why taking parameters rather then interface as a parameter
     *
     * @param string $cstmIntgSeq
     * @param string $firstName
     * @param string $lastName
     * @param string $birthDay
     * @param string $mobileNo
     * @param string $emil
     * @param string $sex
     * @param string $emailYN
     * @param string $smsYN
     * @param string $callYN
     * @param string $dmYN
     * @param string $homeCity
     * @param string $homeState
     * @param string $homeAddr1
     * @param string $homeZip
     * @param string $statusCD
     * @param string $salOrgCd
     * @param string $salOffCd
     * @return \Amore\CustomerRegistration\Api\Data\ResponseInterface
     */
    public function update(
        $cstmIntgSeq,
        $firstName,
        $lastName,
        $birthDay,
        $mobileNo,
        $email,
        $sex,
        $emailYN,
        $smsYN,
        $callYN,
        $dmYN,
        $homeCity,
        $homeState,
        $homeAddr1,
        $homeZip,
        $statusCD,
        $salOrgCd,
        $salOffCd
    ) {
        try {
            $mobileNo = str_replace("-", "", $mobileNo);
            $homeCityName = '';
            $homeStateName = '';
            $parameters = [
                'cstmIntgSeq' => $cstmIntgSeq,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'birthDay' => $birthDay,
                'mobileNo' => $mobileNo,
                'email' => $email,
                'sex' => $sex,
                'emailYN' => $emailYN,
                'smsYN' => $smsYN,
                'callYN' => $callYN,
                'dmYN' => $dmYN,
                'homeCity' => $homeCity,
                'homeState' => $homeState,
                'homeAddr1' => $homeAddr1,
                'homeZip' => $homeZip,
                'statusCD' => $statusCD,
                'salOrgCd' => $salOrgCd,
                'salOffCd' => $salOffCd
            ];
            $callSuccess = 1;
            $response = '';
            $this->logger->addAPICallLog(
                'Customer update api call',
                '{Base URL}/rest/all/V1/pos-customers/',
                $parameters
            );

            $customerWebsiteId = $this->getCustomerWebsiteId($salOffCd);

            if ($response == '' && $customerWebsiteId == 0) {
                $response = $this->getResponse(
                    "0001",
                    'No website exist against sales office code '.$salOffCd,
                    '0001',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if ($response == '' && !trim($cstmIntgSeq)) {
                $response = $this->getResponse(
                    "0002",
                    'Customer Sequence Number can not be empty '.$cstmIntgSeq,
                    '0002',
                    'NO',
                    $cstmIntgSeq
                );
            }

            /**
             * @var \Magento\Customer\Model\Data\Customer $customer
             */
            $customers = $this->getCustomerByIntegraionNumber($cstmIntgSeq, $customerWebsiteId);

            if ($response == '' && !count($customers)) {
                $response =  $this->getResponse(
                    "0003",
                    'No customer exist against this integration sequence '.$cstmIntgSeq,
                    '0003',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if ($response == '' && count($customers) > 1) {
                $response = $this->getResponse(
                    "0004",
                    'There are more than one customer exist against this sequence Id '.
                    $cstmIntgSeq.' in website '.$customerWebsiteId,
                    '0004',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if ($response == '' && trim($mobileNo) &&
                !preg_match('/^[0-9-]+$/', $mobileNo)) {
                $response = $this->getResponse(
                    "0006",
                    $mobileNo.' Mobile number can contain only number and hypens ',
                    '0006',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if ($response == '' && trim($mobileNo) &&
                $this->mobileUseByOtherCustomer($cstmIntgSeq, $customerWebsiteId, $mobileNo)) {
                $response = $this->getResponse(
                    "0005",
                    $mobileNo.' Mobile number is assigned to other customer in website '.$customerWebsiteId,
                    '0005',
                    'NO',
                    $cstmIntgSeq
                );
            }

            /** @var \Magento\Directory\Model\Region $region */
            $region = $this->regionFactory->create();

            if ($response == '' && $homeCity != '') {
                $this->regionResourceModel->load($region, $homeCity, 'code');
                if (!$region->getDefaultName()) {
                    $response = $this->getResponse(
                        "0007",
                        'There is not city name against the code ' . $homeCity,
                        '0007',
                        'NO',
                        $cstmIntgSeq
                    );
                } else {
                    $homeCityName = $region->getDefaultName();
                }
            }

            if ($response == '' && trim($homeCity) == '' && trim($homeState) != '') {
                $response = $this->getResponse(
                    "0008",
                    'If you want to set the state then city is required for state code ' . $homeState,
                    '0008',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if ($response == '' && $region->getRegionId() && $homeState != '') {
                $cityName = '';
                $cities = $this->cityHelper->getCityData();
                $regionCities = $cities[$region->getRegionId()];
                foreach ($regionCities as $regionCity) {
                    if ($regionCity['code'] == $homeState) {
                        $cityName = $regionCity['name'];
                        break;
                    }
                }
                if ($cityName == '') {
                    $response = $this->getResponse(
                        "0009",
                        'There is no state name against the code ' . $homeState,
                        '0009',
                        'NO',
                        $cstmIntgSeq
                    );
                } else {
                    $homeStateName = $cityName;
                }
            }

            if ($response == '') {
                $customer = $customers[0];
                trim($firstName) ? $customer->setFirstname($firstName) : '';
                trim($lastName) ? $customer->setLastname($lastName) : '';
                trim($birthDay) ? $customer->setDob($this->setProperDateFormat($birthDay)) : '';
                trim($mobileNo) ? $customer->setCustomAttribute('mobile_number', $mobileNo) : '';
                trim($email) ? $customer->setEmail($email) : '';
                trim($sex) ? $customer->setGender($sex == 'M' ? 1 : 2) : '';
                trim($smsYN) ? $customer->setCustomAttribute('sms_subscription_status', $smsYN == 'Y' ? 1 : 0) : '';
                trim($dmYN) ? $customer->setCustomAttribute('dm_subscription_status', $dmYN == 'Y' ? 1 : 0) : '';
                trim($callYN) ? $customer->setCustomAttribute('call_subscription_status', $callYN == 'Y' ? 1 : 0) : '';
                trim($homeCity) ? $customer->setCustomAttribute('dm_state', $homeCityName) : '';
                trim($homeState) ? $customer->setCustomAttribute('dm_city', $homeStateName) : '';
                trim($homeAddr1) ? $customer->setCustomAttribute('dm_detailed_address', $homeAddr1) : '';
                trim($homeZip) ? $customer->setCustomAttribute('dm_zipcode', $homeZip) : '';
                trim($statusCD) ? $customer->setCustomAttribute('status_code', $statusCD == '1' ? 1 : 0) : '';

                //Confiremd with Client sales office and organization code will never change
                //trim($salOrgCd)? $customer->setCustomAttribute('sales_organization_code', $salOrgCd):'';
                //trim($salOffCd)?$customer->setCustomAttribute('sales_office_code', $salOffCd):'';
                //trim($prtnrid)?$customer->setCustomAttribute('partner_id', $prtnrid):'';

                $customer = $this->customerRepositoryInterface->save($customer);
                if (trim($emailYN) == 'Y') {
                    $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                } elseif (trim($emailYN) == 'N') {
                    // $subscriber = $this->subscriberFactory->create();
                    //$this->subscriberFactory->create()->unsubscribeCustomerById(35);
                    //$temp = $subscriber->loadByEmail('iiqra@eguanacommerce.com')->getCustomerId();
                    //$subscriber->unsubscribe();
                }
                $response = $this->getResponse("0000", 'SUCCESS', '200', 'OK', $cstmIntgSeq);
            }
        } catch (\Exception $e) {
            $callSuccess = 1;
            $response = $this->getResponse($e->getCode(), $e->getMessage(), $e->getCode(), 'NO', $cstmIntgSeq);
        }

        $responseData = $response->getData();

        $arrayResponse = [
            'code' => $response->getCode(),
            'message' => $response->getMessage(),
            'data' => [
                'status_code' => $responseData->getStatusCode(),
                'status_message' => $responseData->getStatusMessage(),
                'cstm_intg_seq' => $responseData->getCstmIntgSeq()
            ]
        ];

        $log['request'] = $parameters;
        $log['response'] = $arrayResponse;

        $this->eventManager->dispatch(
            'eguana_bizconnect_operation_processed',
            [
                'topic_name' => 'eguana.pos.update.customer',
                'direction' => 'incoming',
                'to' => 'base', //from or to
                'serialized_data' => $this->json->serialize($log),
                'status' => $callSuccess,
                'result_message' => $response->getMessage()
            ]
        );

        return $response;
    }

    private function getResponse($code, $message, $statusCode, $statusMessage, $cstmIntgSeq)
    {
        $logResponse = [
          'code' => $code,
          'message' => $message,
          'data' => [
              'status_code' => $statusCode,
              'status_message' => $statusMessage,
              'cstm_intg_seq' => $cstmIntgSeq
          ]
        ];

        $this->logger->addAPICallLog(
            'Customer update api response',
            '{Base URL}/rest/all/V1/pos-customers/',
            $logResponse
        );

        $this->response->setCode($code);
        $this->response->setMessage($message);
        $this->dataResponse->setCstmIntgSeq($cstmIntgSeq);
        $this->dataResponse->setStatusCode($statusCode);
        $this->dataResponse->setStatusMessage($statusMessage);
        $this->response->setData($this->dataResponse);

        return $this->response;
    }

    private function getCustomerWebsiteId($salOffCd)
    {
        $customerWebsiteId = 0;
        $websiteIds = $this->getWebsiteIds();
        /**
         * Magento core also use the arsort function
         * vendor/magento/module-dhl/Model/Carrier.php at LINE 856
         */
        arsort($websiteIds);
        foreach ($websiteIds as $websiteId) {
            $officeSaleCode = $this->configHelper->getOfficeSalesCode($websiteId);
            if ($officeSaleCode == $salOffCd) {
                $customerWebsiteId = $websiteId;
                break;
            }
        }
        return $customerWebsiteId;
    }

    private function getWebsiteIds()
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = [];

        foreach ($stores as $store) {
            $websiteIds[] = $store["website_id"];
        }

        return $websiteIds;
    }

    private function setProperDateFormat($date)
    {
        $date = substr_replace($date, '/', 4, 0);
        $date = substr_replace($date, '/', 7, 0);

        return $date;
    }

    private function getCustomerByIntegraionNumber($integrationNumber, $websiteId)
    {

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('website_id', $websiteId)
            ->addFilter('integration_number', $integrationNumber)
            ->create();
        $customers = $this->customerRepositoryInterface->getList($searchCriteria)->getItems();
        return $customers;
    }

    private function mobileUseByOtherCustomer($integrationNumber, $websiteId, $mobileNumber)
    {

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('website_id', $websiteId)
            ->addFilter('integration_number', $integrationNumber, 'neq')
            ->addFilter('mobile_number', $mobileNumber)
            ->create();
        $customers = $this->customerRepositoryInterface->getList($searchCriteria)->getItems();

        return count($customers)?true:false;
    }

}
