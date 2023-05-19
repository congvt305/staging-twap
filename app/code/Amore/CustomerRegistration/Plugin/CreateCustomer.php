<?php

namespace Amore\CustomerRegistration\Plugin;

use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Store\Model\StoreRepository;
use Amore\CustomerRegistration\Logger\Logger;
use Magento\Framework\Webapi\Rest\Request;

class CreateCustomer
{
    const IS_POS = 'isPos';
    const SALOFFCD= 'salOffCd';

   /**
     * @var Data
     */
    private Data $configHelper;

    /**
     * @var StoreRepository
     */
    private StoreRepository $storeRepository;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var \Eguana\Directory\Model\ResourceModel\City\CollectionFactory
     */
    private $cityCollectionFactory;

    /**
     * @param Logger $logger
     * @param Data $configHelper
     * @param StoreRepository $storeRepository
     * @param Request $request
     */
    public function __construct(
        Logger $logger,
        Data $configHelper,
        StoreRepository $storeRepository,
        Request $request,
        \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->request = $request;
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    /**
     * assign website for customer
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param $password
     * @param $redirectUrl
     * @return array
     */
    public function beforeCreateAccount(
        AccountManagement $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        try {
            $data = $this->request->getRequestData();
            if ($data && isset($data[self::IS_POS]) && isset($data[self::SALOFFCD]) && $data[self::IS_POS] == 1) {
                $customerWebsiteId = $this->getCustomerWebsiteId($data[self::SALOFFCD]);
                $customer->setWebsiteId($customerWebsiteId);
                if ($addresses = $customer->getAddresses()) {
                    foreach ($addresses as $address) {
                        if ($address->getCity()) {
                            $cityCollection = $this->cityCollectionFactory->create();
                            $cityData = $cityCollection->addFieldToFilter('main_table.default_name', $address->getCity())->getFirstItem();
                            if ($cityData->getRegionId()) {
                                $address->getRegion()->setRegionId($cityData->getRegionId());
                            }
                        }
                    }
                }
                $customer->setCustomAttribute(
                    'sales_organization_code',
                    $this->configHelper->getOrganizationSalesCode($customerWebsiteId)
                );
                $customer->setCustomAttribute(
                    'sales_office_code',
                    $this->configHelper->getOfficeSalesCode($customerWebsiteId)
                );
                $customer->setCustomAttribute('partner_id', $this->configHelper->getPartnerId($customerWebsiteId));
            }

        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
        }
        return [$customer, $password ,$redirectUrl];
    }

    /**
     * @param $salOffCd
     * @return int|mixed
     */
    private function getCustomerWebsiteId($salOffCd)
    {
        $customerWebsiteId = 0;
        $websiteIds = $this->getWebsiteIds();
        asort($websiteIds);
        foreach ($websiteIds as $websiteId) {
            $officeSaleCode = $this->configHelper->getOfficeSalesCode($websiteId);
            if ($officeSaleCode == $salOffCd) {
                $customerWebsiteId = $websiteId;
                break;
            }
        }
        return $customerWebsiteId;
    }

    /**
     * @return array
     */
    private function getWebsiteIds()
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = [];

        foreach ($stores as $store) {
            if ($store->getIsActive() && $store["website_id"]) {
                $websiteIds[] = $store["website_id"];
            }
        }
        return $websiteIds;
    }
}
