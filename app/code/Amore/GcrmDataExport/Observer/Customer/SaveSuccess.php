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

use Amore\GcrmDataExport\Model\Config\Config;
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
use Eguana\Directory\Helper\Data as DataAlias;

/**
 * Call POS API on customer information change
 *
 * Class SaveSuccess
 */
class SaveSuccess implements ObserverInterface
{
    /**
     * @var Config
     */
    private $configData;

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
     * @var DataAlias
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

    /**
     * SaveSuccess constructor.
     * @param Config $configData
     * @param RequestInterface $request
     * @param RegionFactory $regionFactory
     * @param RegionResourceModel $regionResourceModel
     * @param DataAlias $cityHelper
     * @param Sequence $sequence
     * @param Data $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriberFactory $subscriberFactory
     * @param POSLogger $logger
     * @param POSSystem $POSSystem
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        Config $configData,
        RequestInterface $request,
        RegionFactory $regionFactory,
        RegionResourceModel $regionResourceModel,
        DataAlias $cityHelper,
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
        $this->configData = $configData;
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

            $homeCity = '';
            $homeState = '';
            $homeAddr1 = '';
            $homeZip = '';

            try {
                $customerId = $newCustomerData->getId();
                $customerData = $this->customerRepository->getById($customerId);
                $cstmIntgSeq = $customerData->getCustomAttribute('integration_number')->getValue();
                $firstName = $customerData->getFirstname();
                $lastName = $customerData->getLastname();
                $dob = $customerData->getDob();
                $birthDay = str_replace("-", "", $dob);
                $mobileNo = $customerData->getCustomAttribute('mobile_number')->getValue();
                $email = $customerData->getEmail();
                $sex = $customerData->getGender();
                $emailYN = $this->isCustomerSubscribToNewsLetters($customerData->getId()) ? 'Y' : 'N';
                $callYN = $customerData->getCustomAttribute('call_subscription_status')->getValue();
                $smsYN = $callYN;
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
                if ($defaultBillingAddress) {
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
                    }
                }
            } catch (\Exception $e) {
                $this->logger->addExceptionMessage($e->getMessage());
            }

            if (!$oldCustomerData && $newCustomerData) {
                $isNew = '1';
            }
            elseif ($oldCustomerData && $newCustomerData) {
                $isNew = '0';
            }
            try {
                $host = $this->configData->getHerokuHost();
                $dbname = $this->configData->getHerokuDBName();
                $user = $this->configData->getHerokuUser();
                $password = $this->configData->getHerokuPassword();
                $db_connection = pg_connect(
                    "host=$host
                         dbname=$dbname
                         user=$user
                         password=$password"
                );
                if ($db_connection) {
                    $sql = '';
                    $query = "SELECT * FROM customer_test WHERE cstmIntgSeq = '$cstmIntgSeq'";
                    $result = pg_query($db_connection, $query);
                    if ($result && pg_num_rows($result) > 0) {
                        $sql = "UPDATE customer_test SET (cstmIntgSeq,isNew,firstName,lastName,birthDay,mobileNo,email,
                                sex,emailYN,smsYN,callYN,dmYN,homeCity,homeState,homeAddr1,homeZip,statusCD,salOrgCd,
                                salOffCd) = ('$cstmIntgSeq','$isNew','$firstName','$lastName','$birthDay','$mobileNo',
                                '$email','$sex','$emailYN','$smsYN','$callYN','$dmYN','$homeCity','$homeState',
                                '$homeAddr1','$homeZip','$statusCD','$salOrgCd','$salOffCd')
                                 WHERE cstmIntgSeq='$cstmIntgSeq'";
                    } else {
                        $sql = "INSERT INTO customer_test (cstmIntgSeq,isNew,firstName,lastName,birthDay,mobileNo,email,
                            sex,emailYN,smsYN,callYN,dmYN,homeCity,homeState,homeAddr1,homeZip,statusCD,salOrgCd,
                            salOffCd) VALUES ('$cstmIntgSeq','$isNew','$firstName','$lastName','$birthDay','$mobileNo',
                            '$email','$sex','$emailYN','$smsYN','$callYN','$dmYN','$homeCity','$homeState','$homeAddr1',
                            '$homeZip','$statusCD','$salOrgCd','$salOffCd')";
                    }
                    $result = pg_exec($db_connection, $sql);
                    pg_close($db_connection);
                }
            } catch(\Exception $e) {
                $this->logger->addExceptionMessage($e->getMessage());
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

    /**
     * Assign integration number to the customer
     *
     * @param $customer Customer
     * @return mixed
     */
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
            if ($posOrOnline == 'online') {
                $posOrOnline = $customer->getCustomAttribute('imported_from_pos')->getValue() == 1
                    ? 'pos' : 'online';
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
            return $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
            return $customer;
        }
    }
}
