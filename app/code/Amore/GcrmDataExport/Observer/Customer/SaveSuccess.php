<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 7/15/21
 * Time: 3:10 AM
 */
namespace Amore\GcrmDataExport\Observer\Customer;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSystem;
use Magento\Customer\Model\Data\Customer;
use Amore\CustomerRegistration\Model\Sequence;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResourceModel;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Call POS API on customer information change
 * Class SaveSuccess
 */
class SaveSuccess implements ObserverInterface
{
    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * @var POSSystem
     */
    private $POSSystem;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var \Eguana\Directory\Helper\Data
     */
    private $cityHelper;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var RegionResourceModel
     */
    private $regionResourceModel;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var AddressInterfaceFactory
     */
    private $addressDataFactory;

    public function __construct(
        RequestInterface $request,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        \Eguana\Directory\Helper\Data $cityHelper,
        Sequence $sequence,
        Data $config,
        CustomerRepositoryInterface $customerRepository,
        SubscriberFactory $subscriberFactory,
        POSLogger $logger,
        POSSystem $POSSystem,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->sequence = $sequence;
        $this->POSSystem = $POSSystem;
        $this->subscriberFactory = $subscriberFactory;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->cityHelper = $cityHelper;
        $this->request = $request;
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->groupRepository = $groupRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Observer called on successfull customer registration
     *
     * @param Observer $observer
     */
    public function execute(
        Observer $observer
    ) {
        try {
            /**
             * @var Customer $newCustomerData
             */
            $newCustomerData = $observer->getEvent()->getData('customer_data_object');

            /**
             * @var Customer $oldCustomerData
             */
            $oldCustomerData = $observer->getEvent()->getData('orig_customer_data_object');
            if(!$oldCustomerData && $newCustomerData) {
                $id = $newCustomerData->getId();
                $customerData = $this->customerRepository->getById($id);
                $cstmIntgSeq = $customerData->getCustomAttribute('integration_number')->getValue();
                $isNew = 1;
                $firstName = $customerData->getFirstname();
                $lastName = $customerData->getLastname();
                $birthDay = $customerData->getDob();
                $mobileNo = $customerData->getCustomAttribute('mobile_number')->getValue();
                $email = $customerData->getEmail();
                $sex = $customerData->getGender();
                $emailYN = $this->isCustomerSubscribToNewsLetters($customerData->getId()) ? 'Y' : 'N';
                $callYN = $customerData->getCustomAttribute('call_subscription_status')->getValue();
                $smsYN = '0';
                $dmYN = $customerData->getCustomAttribute('dm_subscription_status')->getValue();
                $defaultBillingAddressId = $customerData->getDefaultBilling();
                $addresses = $customerData->getAddresses();
                $defaultBillingAddress = null;
                foreach ($addresses as $address) {
                    if ($address->getId() == $defaultBillingAddressId) {
                        $defaultBillingAddress = $address;
                        break;
                    }
                }
                $statusCD = $customerData->getCustomAttribute('status_code')->getValue();
                $salOrgCd = $customerData->getCustomAttribute('sales_organization_code')->getValue();
                $salOffCd = $customerData->getCustomAttribute('sales_office_code')->getValue();
                $homeCity = $defaultBillingAddress->getRegion()->getRegionCode();
                $homeAddr1 = implode(' ', $defaultBillingAddress->getStreet());
                $homeZip = $defaultBillingAddress->getPostcode();
                $cityName = $defaultBillingAddress->getCity();
                if ($cityName) {
                    $cities = $this->cityHelper->getCityData();
                    $regionCities = $cities[$defaultBillingAddress->getRegionId()];
                    foreach ($regionCities as $regionCity) {
                        if ($regionCity['name'] == $cityName) {
                            $homeState = $regionCity['pos_code'];
                            break;
                        }
                    }
                } else {
                    $homeState = '';
                }
            }
            elseif ($oldCustomerData && $newCustomerData) {
                $id = $newCustomerData->getId();
                $customerData = $this->customerRepository->getById($id);
                $cstmIntgSeq = $customerData->getCustomAttribute('integration_number')->getValue();
                $isNew = 1;
                $firstName = $customerData->getFirstname();
                $lastName = $customerData->getLastname();
//                $birthDay = $customerData->getDob();
                $birthDay = '20212021';
                $mobileNo = $customerData->getCustomAttribute('mobile_number')->getValue();
                $email = $customerData->getEmail();
                $sex = $customerData->getGender();
                $emailYN = $this->isCustomerSubscribToNewsLetters($customerData->getId()) ? 'Y' : 'N';
                $callYN = $customerData->getCustomAttribute('call_subscription_status')->getValue();
                $smsYN = '0';
                $dmYN = $customerData->getCustomAttribute('dm_subscription_status')->getValue();
                $defaultBillingAddressId = $customerData->getDefaultBilling();
                $addresses = $customerData->getAddresses();
                $defaultBillingAddress = null;
                foreach ($addresses as $address) {
                    if ($address->getId() == $defaultBillingAddressId) {
                        $defaultBillingAddress = $address;
                        break;
                    }
                }
                $statusCD = $customerData->getCustomAttribute('status_code')->getValue();
                $salOrgCd = $customerData->getCustomAttribute('sales_organization_code')->getValue();
                $salOffCd = $customerData->getCustomAttribute('sales_office_code')->getValue();
                $homeCity = $defaultBillingAddress->getRegion()->getRegionCode();
                $homeAddr1 = implode(' ', $defaultBillingAddress->getStreet());
                $homeZip = $defaultBillingAddress->getPostcode();
                $cityName = $defaultBillingAddress->getCity();
                if ($cityName) {
                    $cities = $this->cityHelper->getCityData();
                    $regionCities = $cities[$defaultBillingAddress->getRegionId()];
                    foreach ($regionCities as $regionCity) {
                        if ($regionCity['name'] == $cityName) {
                            $homeState = $regionCity['pos_code'];
                            break;
                        }
                    }
                } else {
                    $homeState = '';
                }
            }
            try {
                $db_connection = pg_connect(
                    "host=ec2-54-227-246-76.compute-1.amazonaws.com
                         dbname=d44f65jrcq1nqg
                         user=juzvzeaasephfx
                         password=d3f17eed3e5f086472bcafe55d8d1193ab9ff4f93688bac3fafea154bae5a2e3"
                );
                var_dump("Connected successfully");
                $sql = "INSERT INTO customer_test (
                        cstmIntgSeq,isNew,firstName,lastName,birthDay,mobileNo,email,sex,emailYN,
                        smsYN,callYN,dmYN,homeCity,homeState,homeAddr1,homeZip,statusCD,salOrgCd,salOffCd
                       ) VALUES (
                        '$cstmIntgSeq','$isNew','$firstName','$lastName','$birthDay','$mobileNo','$email','$sex','$emailYN',
                        '$smsYN','$callYN','$dmYN','$homeCity','$homeState','$homeAddr1','$homeZip','$statusCD','$salOrgCd','$salOffCd')";
                $result = pg_exec($db_connection, $sql);
                if ($result) {
                    echo "Data inserted Successfully.<br>";
                    $res = pg_exec($db_connection, "SELECT * FROM customer_test");
                    if ($res && pg_numrows($res) > 0) {
                        echo "Data in table : customer_test : Found.<br>";
                        echo "The query executed successfully.<br>";
//                        for ($row = 0; $row < pg_numrows($res); $row++) {
//                            echo pg_result($res, $row,'cstmIntgSeq');
//                        }
                    } else {
                        echo "No Data in table : customer_test : Found.<br>";
                    }
                } else {
                    echo "The query failed with the following error:<br>";
                    echo "Data Could not be inserted Successfully.<br>";
                    echo pg_errormessage($db_connection);
                }
                pg_close($db_connection);
                exit;
            } catch(\Exception $e) {
                var_dump("Connection failed: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
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

    private function assignIntegrationNumber($customer)
    {
        try {
            $groupId = $this->getCustomerGroup($customer);
            if ($groupId) {
                $customer->setGroupId($groupId);
            }

            if ($customer->getCustomAttribute('ba_code')) {
                $baCode = $this->POSSystem->checkBACodePrefix(
                    $customer->getCustomAttribute('ba_code')->getValue()
                );
                $customer->setCustomAttribute('ba_code', $baCode);
            }

            $posOrOnline = 'online';
            /**
             * @Abbas on the request of client. Now if customer register using bar code even than he can be online or
             * offline. So if POS have customer information then he will be considered as offline else online
             */
            /*if ($customer->getCustomAttribute('referrer_code')) {
                $posOrOnline = 'pos';
            }*/
            if ($posOrOnline == 'online') {
                $posOrOnline = $customer->getCustomAttribute('imported_from_pos')->getValue() == 1 ? 'pos' : 'online';
            }

            $this->sequence->setCustomerType($posOrOnline);
            if ($posOrOnline == 'online') {
                $this->sequence->setCustomerWebsiteid($customer->getWebsiteId());
                $secquenceNumber = $this->sequence->getNextValue();
                $customer->setCustomAttribute('integration_number', $secquenceNumber);
            }
            $customer->setCustomAttribute(
                'sales_organization_code',
                $this->config->getOrganizationSalesCode($customer->getWebsiteId())
            );
            $customer->setCustomAttribute(
                'sales_office_code',
                $this->config->getOfficeSalesCode($customer->getWebsiteId())
            );
            //$customer->setCustomAttribute('partner_id', $this->config->getPartnerId($customer->getWebsiteId()));
            return $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
            return $customer;
        }
    }
}
