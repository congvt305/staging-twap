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
     * @param Logger $logger
     * @param Data $configHelper
     * @param StoreRepository $storeRepository
     * @param Request $request
     */
    public function __construct(
        Logger $logger,
        Data $configHelper,
        StoreRepository $storeRepository,
        Request $request
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->request = $request;
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
            if ($this->request->getRequestData() && isset($this->request->getRequestData()[self::IS_POS]) && isset($this->request->getRequestData()[self::SALOFFCD]) && $this->request->getRequestData()[self::IS_POS] == 1) {
                $salOffCd = $this->request->getRequestData()[self::SALOFFCD];
                $customerWebsiteId = $this->getCustomerWebsiteId($salOffCd);
                $customer->setWebsiteId($customerWebsiteId);
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

    /**
     * @return array
     */
    private function getWebsiteIds()
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = [];

        foreach ($stores as $store) {
            if ($store->getIsActive()) {
                $websiteIds[] = $store["website_id"];
            }
        }
        return $websiteIds;
    }
}
