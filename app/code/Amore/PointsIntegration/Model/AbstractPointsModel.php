<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 9:55
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Connection\Request;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreRepository;

abstract class AbstractPointsModel
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
    /**
     * @var StoreRepository
     */
    protected $storeRepository;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractPointsModel constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param StoreRepository $storeRepository
     * @param Request $request
     * @param Config $config
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreRepository $storeRepository,
        Request $request,
        Config $config
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeRepository = $storeRepository;
        $this->request = $request;
        $this->config = $config;
    }

    public function getCustomer($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    public function requestData($customerId, $page = null)
    {
        $customer = $this->getCustomer($customerId);
        $customerIntegrationNumber = $customer->getCustomAttribute('integration_number');

        $websiteId = $customer->getWebsiteId();
        $salOrgCd = $this->config->getOrganizationSalesCode($websiteId);
        $salOffCd = $this->config->getOfficeSalesCode($websiteId);

        if (!empty($page)) {
            return [
                'salOrgCd' => $salOrgCd,
                'salOffCd' => $salOffCd,
                'cstmIntgSeq' => $customerIntegrationNumber,
                'page' => $page
            ];
        } else {
            return [
                'salOrgCd' => $salOrgCd,
                'salOffCd' => $salOffCd,
                'cstmIntgSeq' => $customerIntegrationNumber,
            ];
        }
    }
}
