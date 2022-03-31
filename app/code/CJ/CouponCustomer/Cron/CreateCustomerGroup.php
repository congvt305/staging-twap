<?php

namespace CJ\CouponCustomer\Cron;

use Magento\Customer\Model\GroupFactory;
use Amore\PointsIntegration\Model\Connection\Request;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;
use CJ\CouponCustomer\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

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

    protected $json;


    /**
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        GroupFactory          $groupFactory,
        Request               $request,
        Logger                $logger,
        Data                  $helperData,
        StoreManagerInterface $storeManager,
        Json $json
    ){
        $this->groupFactory = $groupFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->json = $json;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->helperData->isEnableCronjob()) {
            $posCustomerGrades = null;
            try {
                $posCustomerGrades = $this->getAllPOSCustomerGrade();
                if(isset($posCustomerGrades)) {
                    foreach ($posCustomerGrades as $posCustomerGrade) {
                        $posCustomerGradeName = $posCustomerGrade['cstmGradeNM'];
                        if (!$this->helperData->isCreatedCustomerGroup($posCustomerGradeName)) {
                            $group = $this->groupFactory->create();
                            $group->setCode($posCustomerGradeName)->save();
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
     * prepare request data
     * @return void
     */

    private function getAllPOSCustomerGrade()
    {
        $customerGrades = null;
        try {
            $requestData = '';
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $responseData = $this->request->sendRequest($requestData, $websiteId, self::POS_ALL_CUSTOMER_GRADE_TYPE);
//            $response = '{
//          "success": true,
//          "data": {
//            "statusCode": "200",
//            "statusMessage": "ok",
//            "csmGradeData": [
//              {
//                "cstmGradeCD": "TWL0003",
//                "cstmGradeNM": "Snow Crystal"
//              },
//              {
//                "cstmGradeCD": "VNL0001",
//                "cstmGradeNM": "Guest"
//              },
//              {
//                "cstmGradeCD": "TWS0014",
//                "cstmGradeNM": "Snow Diamond"
//              },
//              {
//                "cstmGradeCD": "TWS0014",
//                "cstmGradeNM": "VIPPPDatTest"
//              }
//            ]
//          }
//        }';
            // $responseData = $this->json->unserialize($response);
            if (isset($responseData['data']['csmGradeData'])) {
                $customerGrades = $responseData['data']['csmGradeData'];
            }
        }catch (\Exception $exception) {
            $this->logger->info("FAIL TO GET ALL POS CUSTOMER GRADES");
            $this->logger->error($exception->getMessage());
        }
        return $customerGrades;

    }

}
