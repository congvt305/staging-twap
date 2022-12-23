<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use CJ\CouponCustomer\Helper\Data;
use CJ\CustomCustomer\Api\Data\CustomerDataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
     * @var \CJ\CustomCustomer\Api\Data\SyncGradeResponseInterface
     */
    protected $syncGradeResponse;

    /**
     * @var \CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseFactory
     */
    protected $updateCustomerGroupResponseFactory;

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
        \CJ\CustomCustomer\Api\Data\SyncGradeResponseInterface $syncGradeResponse,
        \CJ\CustomCustomer\Model\CustomerGroup\UpdateCustomerGroupResponseFactory $updateCustomerGroupResponseFactory
    )
    {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->helper = $helper;
        $this->storeRepository = $storeRepository;
        $this->json = $json;
        $this->syncGradeResponse = $syncGradeResponse;
        $this->updateCustomerGroupResponseFactory = $updateCustomerGroupResponseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroup(array $body)
    {
        $loggingCheck = $this->helper->getLoggingEnabled();

        $result = [
            'code' => '0000',
            'message' => 'SUCCESS',
            'data' => []
        ];

        $parameters = ['body' => $body];

        if ($loggingCheck) {
            $this->logger->info('***** SYNC CUSTOMER GRADE API PARAMETERS *****');
            $this->logger->info($this->json->serialize($parameters));
        }

        foreach ($body as $reqItem) {
            /** @var CustomerDataInterface $info */
            $info = $reqItem->getGradeData();
            $cstmIntegSeq = $info[CustomerDataInterface::CSTM_INTG_SEQ];
            $cstmGradeCD = $info[CustomerDataInterface::CSTM_GRADE_C_D];
            $cstmGradeNM = $info[CustomerDataInterface::CSTM_GRADE_N_M];

            if ($customerData = $this->getCustomerByIntgSeq($cstmIntegSeq)) {
                $groupName = $this->helperData->getPrefix($cstmGradeCD) . '_' . $cstmGradeNM;
                $groupId = $this->helperData->getCustomerGroupIdByName($groupName);

                if (!$groupId) {
                    $message = __("Failed to update new grade for successful customer. Error because this customer group \"%1\" does not exist on magento", $groupName);
                    $result['data'][] = $this->getUpdateCustomerGroupResult($info, $message, "0003");
                } elseif ($groupId == $customerData->getGroupId()) {
                    $message = __("Failed to update new grade because this customer's grade \"%1\" on magento synced with grade on POS already", $groupName);
                    $result['data'][] = $this->getUpdateCustomerGroupResult($info, $message, "0004");
                } else {
                    $customerData->setGroupId($groupId);
                    $this->customerRepository->save($customerData);
                    $message = __("Updated the latest grade for customer \"%1\"", $cstmIntegSeq);
                    $result['data'][] = $this->getUpdateCustomerGroupResult($info, $message, "0001");
                }
            } else {
                $message = __("Failed to update grade. Error is no customer exists with integration sequence \"%1\"", $cstmIntegSeq);
                $result['data'][] = $this->getUpdateCustomerGroupResult($info, $message, "0002");
            }
        }

        return $this->getSyncGradeResponse(
            $result['code'],
            $result['message'],
            $result['data'],
            $loggingCheck
        );
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
            throw new LocalizedException(__('customer not found'));
        }
        return $this->customerRepository->getById($customer->getId());
    }

    /**
     * @param $request
     * @param $message
     * @param $code
     * @param $exceptionMsg
     * @return \CJ\CustomCustomer\Model\CustomerGroup\UpdateCustomerGroupResponse
     */
    protected function getUpdateCustomerGroupResult($request, $message, $code)
    {
        $result = $this->updateCustomerGroupResponseFactory->create();
        $result->setCode($code);
        $result->setMessage($message);
        $result->setSuccess($code === "0001");
        return $result;
    }

    /**
     * @param string $code
     * @param string $message
     * @param \CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseInterface[] $data
     * @param bool $loggingCheck
     * @return \CJ\CustomCustomer\Api\Data\SyncGradeResponseInterface
     */
    protected function getSyncGradeResponse($code, $message, $data = [], $loggingCheck = false) {
        $response = [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];

        if ($loggingCheck) {
            $this->logger->info('***** SYNC CUSTOMER GRADE API RESPONSE *****');
            $this->logger->info($this->json->serialize($response));
        }

        $this->syncGradeResponse->setCode($code);
        $this->syncGradeResponse->setMessage($message);
        $this->syncGradeResponse->setData($data);

        return $this->syncGradeResponse;
    }
}
