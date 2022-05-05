<?php

namespace Amore\CustomerRegistration\Plugin;

use Amore\CustomerRegistration\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Store\Model\StoreRepository;
use Magento\Framework\App\RequestInterface;
use Amore\CustomerRegistration\Logger\Logger;

class CreateCustomer
{
    /**
     * @var Data
     */
    private Data $configHelper;

    /**
     * @var StoreRepository
     */
    private StoreRepository $storeRepository;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Logger $logger
     * @param Data $configHelper
     * @param StoreRepository $storeRepository
     * @param RequestInterface $request
     */
    public function __construct(
        Logger $logger,
        Data $configHelper,
        StoreRepository $storeRepository,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->storeRepository = $storeRepository;
        $this->request = $request;
    }

    /**
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
            if ($this->request && $this->request->getContent() && json_decode($this->request->getContent())->isPos == 1) {
                $salOffCd = json_decode($this->request->getContent())->salOffCd;
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
