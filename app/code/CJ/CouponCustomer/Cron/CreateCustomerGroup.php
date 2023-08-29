<?php

namespace CJ\CouponCustomer\Cron;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Customer\Model\GroupFactory;
use CJ\Middleware\Model\PosRequest as MiddlewareRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;
use CJ\CouponCustomer\Helper\Data;
use Psr\Log\LoggerInterface;

class CreateCustomerGroup extends MiddlewareRequest
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param LoggerInterface $logger
     * @param Config $config
     * @param GroupFactory $groupFactory
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        LoggerInterface $logger,
        Config $config,
        GroupFactory $groupFactory,
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->groupFactory = $groupFactory;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
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
            $responseData = $this->sendRequest($requestData, $websiteId, self::POS_ALL_CUSTOMER_GRADE_TYPE);
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
