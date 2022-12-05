<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;

class CustomerGroupManagement implements \CJ\CustomCustomer\Api\CustomerGroupManagementInterface {

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \CJ\CustomCustomer\Logger\Logger
     */
    protected $logger;

    /**
     * @var Json
     */
    protected $json;

    protected $customerPointsSearch;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \CJ\CustomCustomer\Logger\Logger $logger
     * @param Json $json
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \CJ\CustomCustomer\Logger\Logger $logger,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch,
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->json = $json;
        $this->customerPointsSearch = $customerPointsSearch;
    }


    public function setGroup($customerGradeData)
    {
        $result = [];

        $customerIntegrationId = $customerGradeData['integrationNumber'];
        $currentGrade = $customerGradeData['currentGrade'];

        $customer = $this->getCustomerByIntegrationNumber($customerIntegrationId);

        if ($customer) {
            //@todo
        }



    }

    protected function getCustomerByIntegrationNumber($integrationNumber)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('integration_number', $integrationNumber)
            ->create();
        $customers = $this->customerRepository->getList($searchCriteria)->getItems();
        $customer = reset($customers);

        return $customer;
    }

}
