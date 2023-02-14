<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use Amore\Sap\Api\Data\SapOrderStatusInterface;
use CJ\CouponCustomer\Helper\Data;
use CJ\CustomCustomer\Api\Data\CustomerDataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

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
        \CJ\CustomCustomer\Helper\Data                    $helper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->helper = $helper;
        $this->storeRepository = $storeRepository;
        $this->json = $json;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroup(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $gradeData)
    {
        $loggingCheck = $this->helper->getLoggingEnabled();

        $result = [];

        $parameters = $gradeData->toArray();

        if ($loggingCheck) {
            $this->logger->info('***** SYNC CUSTOMER GRADE API PARAMETERS *****');
            $this->logger->info($this->json->serialize($parameters));
        }

        $cstmIntegSeq = $gradeData[CustomerDataInterface::CSTM_INTG_SEQ];
        $cstmGradeCD = $gradeData[CustomerDataInterface::CSTM_GRADE_C_D];
        $cstmGradeNM = $gradeData[CustomerDataInterface::CSTM_GRADE_N_M];

        try {
            if ($customerData = $this->getCustomerByIntgSeq($cstmIntegSeq)) {
                $groupName = $this->helperData->getPrefix($cstmGradeCD) . '_' . $cstmGradeNM;
                $groupId = $this->helperData->getCustomerGroupIdByName($groupName);

                if (!$groupId) {
                    $message = __("Failed to update new grade for successful customer. Error because this customer group \"%1\" does not exist on magento", $groupName);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0001");
                } elseif ($groupId == $customerData->getGroupId()) {
                    $message = __("Failed to update new grade because this customer's grade \"%1\" on magento synced with grade on POS already", $groupName);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0002");
                } else {
                    $customerData->setGroupId($groupId);
                    $this->customerRepository->save($customerData);
                    $message = __("Updated the latest grade for customer \"%1\"", $cstmIntegSeq);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0000");
                }
            } else {
                $message = __("Failed to update grade. Error is no customer exists with integration sequence \"%1\"", $cstmIntegSeq);
                $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $message, "0001");
            }
        } catch (\Exception $e) {
            $result[$cstmIntegSeq] = $this->gradeResultMsg($gradeData, $e->getMessage(), "0001");
        }

        $this->operationLogWriter($parameters, $result, $gradeData, 'cj.customer.group.sync.info');

        return $result;
    }

    /**
     * @param string $cstmIntgSeq
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws LocalizedException
     */
    protected function getCustomerByIntgSeq($cstmIntgSeq)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('website_id', $this->helper->getWebsiteIdByIntgSeq($cstmIntgSeq))
            ->addFilter('integration_number', $cstmIntgSeq)
            ->create();
        $customers = $this->customerRepository->getList($searchCriteria)->getItems();
        $customer = reset($customers);

        if (!$customer) {
            throw new LocalizedException(__('No customer found with this integration sequence number: "%1"', $cstmIntgSeq));
        }
        return $this->customerRepository->getById($customer->getId());
    }

    /**
     * @param CustomerDataInterface $customerData
     * @param string $message
     * @param string $code
     * @return array
     */
    protected function gradeResultMsg(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $customerData, $message, $code)
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $customerData->toArray()
        ];
    }


    /**
     * @param array $parameters
     * @param array $result
     * @param CustomerDataInterface $customerData
     * @param $topicName
     * @return void
     */
    public function operationLogWriter(array $parameters, array $result, \CJ\CustomCustomer\Api\Data\CustomerDataInterface $customerData, $topicName)
    {
        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => $topicName,
                'direction' => 'incoming',
                'to' => "Magento",
                'serialized_data' => $this->json->serialize($parameters),
                'status' => $this->setOperationLogStatus($result[$customerData->getCstmIntgSeq()]['code']),
                'result_message' => $this->json->serialize($result)
            ]
        );
    }

    /**
     * @param $code
     * @return int
     */
    protected function setOperationLogStatus($code)
    {
        switch ($code) {
            case "0001":
                $result = 0;
                break;
            case "0000":
                $result = 1;
                break;
            case "0002":
                $result = 2;
                break;
            default:
                $result = 0;
        }
        return $result;
    }
}
