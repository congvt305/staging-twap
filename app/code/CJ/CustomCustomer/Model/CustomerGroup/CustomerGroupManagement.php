<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use CJ\CouponCustomer\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class CustomerGroupManagement
 */
class CustomerGroupManagement implements \CJ\CustomCustomer\Api\CustomerGroupManagementInterface
{

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \CJ\CustomCustomer\Logger\Logger
     */
    protected $logger;

    /**
     * @var \CJ\CouponCustomer\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \CJ\CustomCustomer\Helper\Data
     */
    protected $helper;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \CJ\CustomCustomer\Logger\Logger $logger
     * @param Data $helperData
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \CJ\CustomCustomer\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder      $searchCriteriaBuilder,
        \CJ\CustomCustomer\Logger\Logger                  $logger,
        \CJ\CouponCustomer\Helper\Data                    $helperData,
        \Magento\Store\Api\StoreRepositoryInterface       $storeRepository,
        \CJ\CustomCustomer\Helper\Data                    $helper
    )
    {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->helper = $helper;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setGroup($gradeData)
    {
        $result = [];

        $cstmIntegSeq = $gradeData['cstmIntgSeq'];
        $scope = $gradeData['scope'];
        $websiteId = $this->storeRepository->get($scope)->getWebsiteId();
        $loggingEnabled = $this->helper->getLoggingEnabled();

        $customer = $this->getCustomerByIntegrationNumber($cstmIntegSeq, $websiteId);

        if (!$customer) {
            $message = __("Failed to update grade. Error is no customer exists with integration number \"%1\"", $cstmIntegSeq);
            $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0002", $message);
        } else {
            $customerId = $customer->getId();
            $customerData = $this->customerRepository->getById($customerId);

            if (isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                $gradeName = $prefix . '_' . $gradeData['cstmGradeNM'];
                $posCustomerGroupId = $this->helperData->getCustomerGroupIdByName($gradeName);

                if (!$posCustomerGroupId) {
                    $message = __("Failed to update new grade for successful customer. Error because this customer group \"%1\" does not exist on magento", $gradeName);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0003");
                } elseif ($posCustomerGroupId == $customerData->getGroupId()) {
                    $message = __("Failed to update new grade because this customer's grade \"%1\" on magento synced with grade on POS already", $gradeName);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0004");
                } else {
                    $customerData->setGroupId($posCustomerGroupId);
                    $this->customerRepository->save($customerData);
                    $message = __("Updated the latest grade for customer \"%1\"", $cstmIntegSeq);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0001");
                }
            }
        }
        if ($loggingEnabled) {
            $this->logger->info("Response data", [$result]);
        }
        return $result;
    }

    /**
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomerByIntegrationNumber($integrationNumber, $websiteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('website_id', $websiteId)
            ->addFilter('integration_number', $integrationNumber)
            ->create();
        $customers = $this->customerRepository->getList($searchCriteria)->getItems();

        return reset($customers);
    }

    /**
     * @param $request
     * @param $message
     * @param $code
     * @param $exceptionMsg
     * @return array
     */
    protected function gradeResultMsg($request, $message, $code, $exceptionMsg = '')
    {
        return [
            'success' => $code === "0001",
            'code' => $code,
            'message' => $message,
            'exceptionMsg' => $exceptionMsg,
            'data' => [
                'cstmIntgSeq' => $request['cstmIntgSeq'],
                'cstmGradeNM' => $request['cstmGradeNM'],
                'cstmGradeCD' => $request['cstmGradeCD'],
                'scope' => $request['scope']
            ]
        ];
    }
}
