<?php

namespace CJ\CouponCustomer\Cron;

use Magento\Customer\Model\GroupFactory;
use Amore\PointsIntegration\Model\Connection\Request;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;
use CJ\CouponCustomer\Helper\Data;

class CreateCustomerGroup
{
    /**
     * pos all customer grade type
     */
    const POS_ALL_CUSTOMER_GRADE_TYPE = 'customerGrade';
    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;


    /**
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        GroupFactory          $groupFactory,
        Request               $request,
        Logger                $logger,
        Data                  $helperData,
        StoreManagerInterface $storeManager){
        $this->groupFactory = $groupFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->helperData->isEnableCronjob()) {
            try {
                $requestData = '';
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $response = $this->request->sendRequest($requestData, $websiteId, self::POS_ALL_CUSTOMER_GRADE_TYPE);
                $customerGroup = '';
                foreach ($response as $posCustomerGroup) {
                    if (!$this->helperData->isCreatedCustomerGroup($posCustomerGroup)) {
                        $group = $this->groupFactory->create();
                        $group->setCode('Thanh Dat Group')->save();
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info('Fail to create customer group: ' . $exception->getMessage());
                $this->logger->info($customerGroup);
            }
        }
    }

    /**
     * prepare request data
     * @return void
     */
    public function getRequestData()
    {

    }

}
