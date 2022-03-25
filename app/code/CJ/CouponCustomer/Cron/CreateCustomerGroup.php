<?php

namespace CJ\CouponCustomer\Cron;

use Magento\Customer\Model\GroupFactory;
use Amore\PointsIntegration\Model\Connection\Request;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;

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
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        GroupFactory $groupFactory,
        Request      $request,
        Logger       $logger,
    StoreManagerInterface $storeManager){
        $this->groupFactory = $groupFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $requestData = '';
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $response = $this->request->sendRequest($requestData, $websiteId, self::POS_ALL_CUSTOMER_GRADE_TYPE);
            $customerGroup = '';
            $group = $this->groupFactory->create();
            $group->setCode('Thanh Dat Group')->save();
        } catch (\Exception $exception) {
            $this->logger->info('Fail to create customer group: '. $exception->getMessage());
            $this->logger->info($customerGroup);
        }


    }
}
