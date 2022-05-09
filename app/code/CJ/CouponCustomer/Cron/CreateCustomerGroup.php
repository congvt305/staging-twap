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
     * Pos all customer grade type
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
        StoreManagerInterface $storeManager
    ) {
        $this->groupFactory = $groupFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }


    /**
     * todo cronjob create pos customer grade using api
     */
    public function execute()
    {
        if ($this->helperData->isCronCustomerGroupEnabled()) {
            $posCustomerGrades = null;
            try {
                $posCustomerGrades = $this->getAllPOSCustomerGrade();
                if(isset($posCustomerGrades)) {
                    foreach ($posCustomerGrades as $posCustomerGrade) {
                        $posCustomerGradeName = $posCustomerGrade['cstmGradeNM'];
                        $posCustomerGradeCode = $posCustomerGrade['cstmGradeCD'];
                        $prefix = $this->helperData->getPrefix($posCustomerGradeCode);
                        $posCustomerGradeGroup = $prefix.'_'.$posCustomerGradeName;
                        if (!$this->helperData->isCustomerGroupExist($posCustomerGradeGroup)) {
                            $group = $this->groupFactory->create();
                            $group->setCode($posCustomerGradeGroup)->save();
                        }
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info('Fail to create customer group: ' . $exception->getMessage());
                $this->logger->info($posCustomerGrades);
            }
        }
    }

    /**
     * Get all POS customer grade use API
     *
     * @return void
     */

    private function getAllPOSCustomerGrade()
    {
        $customerGrades = null;
        try {
            $requestData = '';
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $responseData = $this->request->sendRequest($requestData, $websiteId, self::POS_ALL_CUSTOMER_GRADE_TYPE);
            if (isset($responseData['data']['csmGradeData'])) {
                $customerGrades = $responseData['data']['csmGradeData'];
            }
        } catch (\Exception $exception) {
            $this->logger->info("FAIL TO GET ALL POS CUSTOMER GRADES");
            $this->logger->error($exception->getMessage());
        }
        return $customerGrades;
    }
}
