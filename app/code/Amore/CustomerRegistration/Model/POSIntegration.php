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

    public function __construct(
        Data $configHelper,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreRepository $storeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
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
     * @return boolean|void
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
        $salOffCd,
        $prtnrid
    ) {
        try {
            $parameters = [
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
                $salOffCd,
                $prtnrid
            ];

            $this->logger->addAPICallLog(
                'Customer update api call',
                '{Base URL}/rest/all/V1/pos-customers/',
                $parameters
            );

            $customerWebsiteId = $this->getCustomerWebsiteId($salOffCd);

            if ($customerWebsiteId == 0) {
                return $this->getResponse(
                    "0001",
                    'No website exist against sales office code '.$salOffCd,
                    '0001',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if (!trim($cstmIntgSeq)) {
                return $this->getResponse(
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

            if (!count($customers)) {
                return $this->getResponse(
                    "0003",
                    'No customer exist against this integration sequence '.$cstmIntgSeq,
                    '0003',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if (count($customers) > 1) {
                return $this->getResponse(
                    "0004",
                    'There are more than one customer exist against this sequence Id '.$cstmIntgSeq.' in website '.$customerWebsiteId,
                    '0004',
                    'NO',
                    $cstmIntgSeq
                );
            }

            if (trim($mobileNo) && $this->mobileUseByOtherCustomer($cstmIntgSeq, $customerWebsiteId, $mobileNo)) {
                return $this->getResponse(
                    "0005",
                    $mobileNo.' Mobile number is assigned to other customer in website '.$customerWebsiteId,
                    '0005',
                    'NO',
                    $cstmIntgSeq
                );
            }
            $customer = $customers[0];
            trim($firstName)?$customer->setFirstname($firstName):'';
            trim($lastName)?$customer->setLastname($lastName):'';
            trim($birthDay)?$customer->setDob($this->setProperDateFormat($birthDay)):'';
            trim($mobileNo)?$customer->setCustomAttribute('mobile_number', $mobileNo):'';
            trim($email)?$customer->setEmail($email):'';
            trim($sex)?$customer->setGender($sex == 'M' ? 1 : 2):'';
            trim($smsYN)?$customer->setCustomAttribute('sms_subscription_status', $smsYN == 'Y' ? 1 : 0):'';
            trim($dmYN)?$customer->setCustomAttribute('dm_subscription_status', $dmYN == 'Y' ? 1 : 0):'';
            trim($callYN)?$customer->setCustomAttribute('call_subscription_status', $callYN == 'Y' ? 1 : 0):'';
            trim($homeCity)?$customer->setCustomAttribute('dm_city', $homeCity):'';
            trim($homeState)?$customer->setCustomAttribute('dm_state', $homeState):'';
            trim($homeAddr1)?$customer->setCustomAttribute('dm_detailed_address', $homeAddr1):'';
            trim($homeZip)?$customer->setCustomAttribute('dm_zipcode', $homeZip):'';
            trim($statusCD)?$customer->setCustomAttribute('status_code', $statusCD == '1' ? 1 : 0):'';
            trim($prtnrid)?$customer->setCustomAttribute('partner_id', $prtnrid):'';
            //Confiremd with Client sales office and organization code will never change
            //trim($salOrgCd)? $customer->setCustomAttribute('sales_organization_code', $salOrgCd):'';
            //trim($salOffCd)?$customer->setCustomAttribute('sales_office_code', $salOffCd):'';

            $customer = $this->customerRepositoryInterface->save($customer);
            if (trim($emailYN) == 'Y') {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            } elseif (trim($emailYN) == 'N') {
               // $subscriber = $this->subscriberFactory->create();
                //$this->subscriberFactory->create()->unsubscribeCustomerById(35);
                //$temp = $subscriber->loadByEmail('iiqra@eguanacommerce.com')->getCustomerId();
                //$subscriber->unsubscribe();
            }

        } catch (\Exception $e) {
            return $this->getResponse($e->getCode(), $e->getMessage(), $e->getCode(), 'NO', $cstmIntgSeq);
        }
        return $this->getResponse("0000", 'SUCCESS', '200', 'OK', $cstmIntgSeq);
    }

    private function getResponse($code, $message, $statusCode, $statusMessage, $cstmIntgSeq)
    {
        $response = [[
            'code' => $code,
            'message' => $message,
            'data'  =>    [
                'statusCode' => $statusCode,
                'statusMessage' => $statusMessage,
                'cstmIntgSeq' => $cstmIntgSeq
            ]
        ]];
        $this->logger->addAPICallLog(
            'Customer update api response',
            '{Base URL}/rest/all/V1/pos-customers/',
            $response
        );
        return $response;
    }

    private function getCustomerWebsiteId($salOffCd)
    {
        $customerWebsiteId = 0;
        $websiteIds = $this->getWebsiteIds();
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
