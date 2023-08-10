<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 9:55
 */

namespace Amore\PointsIntegration\Model;

use CJ\Middleware\Model\Pos\Connection\Request;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreRepository;
use CJ\Middleware\Helper\Data;

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
     * @param Data $middlewareHelper
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreRepository $storeRepository,
        Request $request,
        Config $config,
        Data $middlewareHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeRepository = $storeRepository;
        $this->request = $request;
        $this->config = $config;
        $this->middlewareHelper = $middlewareHelper;
    }

    public function getCustomer($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    public function requestData($customerId, $page = null)
    {
        $customer = $this->getCustomer($customerId);
        if ($customer->getCustomAttribute('integration_number')) {
            $customerIntegrationNumber = $customer->getCustomAttribute('integration_number')->getValue();
        } else {
            $customerIntegrationNumber = '';
        }

        $websiteId = $customer->getWebsiteId();
        $salOrgCd = $this->middlewareHelper->getSalesOrganizationCode('store', $websiteId);
        $salOffCd = $this->middlewareHelper->getSalesOfficeCode('store', $websiteId);

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

    /**
     * Validate the response after get from API
     * @param $response
     * @param $websiteId
     * @return int
     */
    public function responseValidation($response, $websiteId)
    {
        $responseHandled = $this->request->handleResponse($response, $websiteId);
        return $responseHandled && $responseHandled['status'];
    }
}
