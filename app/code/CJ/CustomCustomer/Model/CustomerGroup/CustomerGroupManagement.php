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
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->helper = $helper;
        $this->storeRepository = $storeRepository;
        $this->json = $json;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroup(array $body)
    {
        $loggingCheck = $this->helper->getLoggingEnabled();

        $result = [];

        $parameters = [
            'body' => array_map(function ($_reqItem) {
                return [
                    'gradeData' => $_reqItem->getGradeData()->toArray()
                ];
            }, $body)
        ];

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

            try {
                if ($customerData = $this->getCustomerByIntgSeq($cstmIntegSeq)) {
                    $groupName = $this->helperData->getPrefix($cstmGradeCD) . '_' . $cstmGradeNM;
                    $groupId = $this->helperData->getCustomerGroupIdByName($groupName);

                    if (!$groupId) {
                        $message = __("Failed to update new grade for successful customer. Error because this customer group \"%1\" does not exist on magento", $groupName);
                        $result[$cstmIntegSeq] = $this->gradeResultMsg($reqItem, $message, "0003");
                    } elseif ($groupId == $customerData->getGroupId()) {
                        $message = __("Failed to update new grade because this customer's grade \"%1\" on magento synced with grade on POS already", $groupName);
                        $result[$cstmIntegSeq] = $this->gradeResultMsg($reqItem, $message, "0004");
                    } else {
                        $customerData->setGroupId($groupId);
                        $this->customerRepository->save($customerData);
                        $message = __("Updated the latest grade for customer \"%1\"", $cstmIntegSeq);
                        $result[$cstmIntegSeq] = $this->gradeResultMsg($reqItem, $message, "0001");
                    }
                } else {
                    $message = __("Failed to update grade. Error is no customer exists with integration sequence \"%1\"", $cstmIntegSeq);
                    $result[$cstmIntegSeq] = $this->gradeResultMsg($reqItem, $message, "0002");
                }
            } catch (\Exception $e) {
                $result[$cstmIntegSeq] = $this->gradeResultMsg($reqItem, $e->getMessage(), "0005");
            }
        }



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
            throw new LocalizedException(__('Customer not found!'));
        }
        return $this->customerRepository->getById($customer->getId());
    }

    protected function gradeResultMsg(\CJ\CustomCustomer\Api\Data\SyncGradeReqItemInterface $reqItem, $message, $code)
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $reqItem->getGradeData()->toArray()
        ];
    }
}
