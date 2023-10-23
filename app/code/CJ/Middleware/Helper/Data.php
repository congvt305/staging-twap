<?php
namespace CJ\Middleware\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class Data extends AbstractHelper
{
    const XML_PATH_MIDDLEWARE_ENABLE = 'middleware/general/active';
    const XML_PATH_MIDDLEWARE_URL = 'middleware/general/url';
    const XML_PATH_MIDDLEWARE_USERNAME = 'middleware/general/api_user_id';
    const XML_PATH_MIDDLEWARE_AUTH_KEY = 'middleware/general/auth_key';
    const XML_PATH_MIDDLEWARE_SALES_ORG_CODE = 'middleware/general/sales_organization_code';
    const XML_PATH_MIDDLEWARE_SALES_OFF_CODE = 'middleware/general/sales_office_code';
    const XML_PATH_MIDDLEWARE_MALL_ID = 'middleware/general/mall_id';
    const XML_PATH_MIDDLEWARE_PARTNER_ID = 'middleware/general/partner_id';
    const XML_PATH_MIDDLEWARE_SAP_ORDER_CONFIRM = 'middleware/sap_interface_ids/order_confirm_path';
    const XML_PATH_MIDDLEWARE_SAP_ORDER_CANCEL = 'middleware/sap_interface_ids/order_cancel_path';
    const XML_PATH_MIDDLEWARE_SAP_STOCK_INFO = 'middleware/sap_interface_ids/inventory_stock_path';
    const XML_PATH_MIDDLEWARE_POS_MEMBER_SEARCH = 'middleware/pos_interface_ids/member_search';
    const XML_PATH_MIDDLEWARE_POS_REDEEM_SEARCH = 'middleware/pos_interface_ids/redeem_search';
    const XML_PATH_MIDDLEWARE_POS_CUSTOMER_ORDER = 'middleware/pos_interface_ids/customer_order';
    const XML_PATH_MIDDLEWARE_POS_POINT_SEARCH = 'middleware/pos_interface_ids/point_search';
    const XML_PATH_MIDDLEWARE_POS_POINT_UPDATE = 'middleware/pos_interface_ids/point_update';
    const XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_INFO = 'middleware/customer_interface_ids/member_info';
    const XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_JOIN = 'middleware/customer_interface_ids/member_join';
    const XML_PATH_MIDDLEWARE_CUSTOMER_BACODE_INFO = 'middleware/customer_interface_ids/bacode_info';
    const XML_PATH_IS_DECIMAL_FORMAT = 'middleware/general/is_decimal_format';
    const DISABLE_SAP_INTEGRATION = 'disable_sap_integration';

    const INCLUDE_SHIPPING_AMOUNT_WHEN_SEND_REQUEST_ENABLE_XML_PATH = 'middleware/general/include_shipping_amount_when_send_request';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GetStockSourceLinksInterface
     */
    protected $getStockSourceLinks;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected $sourceItemInterfaceFactory;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        Json $json,
        ResourceConnection $resourceConnection,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks,
        StoreManagerInterface $storeManager,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
        $this->storeManager = $storeManager;
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
        parent::__construct($context);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function isMiddlewareEnabled($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_ENABLE, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMiddlewareURL($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_URL, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMiddlewareUsername($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_USERNAME, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMiddlewareAuthKey($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_AUTH_KEY, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getOrderConfirmInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_ORDER_CONFIRM, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getOrderCancelInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_ORDER_CANCEL, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getStockInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_STOCK_INFO, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMemberSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_MEMBER_SEARCH, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getRedeemSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_REDEEM_SEARCH, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getCustomerSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_CUSTOMER_ORDER, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getPointSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_POINT_SEARCH, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getPointUpdateInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_POINT_UPDATE, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMemberInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_INFO, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMemberJoinInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_JOIN, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getBacodeInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_BACODE_INFO, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getSalesOrganizationCode($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SALES_ORG_CODE, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getSalesOfficeCode($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SALES_OFF_CODE, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getMallId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_MALL_ID, $type, $storeId);
    }

    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getPartnerId($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_MIDDLEWARE_PARTNER_ID,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::XML_PATH_MIDDLEWARE_PARTNER_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }


    /**
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getIsDecimalFormat($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_IS_DECIMAL_FORMAT, $type, $storeId);
    }

    /**
     * @param $customerId
     * @param $page
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequestDataByCustomerId($customerId, $page = null)
    {
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getCustomAttribute('integration_number')) {
            $customerIntegrationNumber = $customer->getCustomAttribute('integration_number')->getValue();
        } else {
            $customerIntegrationNumber = '';
        }

        $websiteId = $customer->getWebsiteId();
        $salOrgCd = $this->getSalesOrganizationCode('store', $websiteId);
        $salOffCd = $this->getSalesOfficeCode('store', $websiteId);

        $result = [
            'salOrgCd' => $salOrgCd,
            'salOffCd' => $salOffCd,
            'cstmIntgSeq' => $customerIntegrationNumber
        ];

        if (!empty($page)) {
            $result['page'] = $page;
        }
        return $result;
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function serializeData($data){
        return $this->json->serialize($data);
    }

    /**
     * @param $data
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserializeData($data){
        if ($data){
            return $this->json->unserialize($data);
        }
        return [];
    }

    /**
     * @param $code
     * @return int
     */
    public function setOperationLogStatus($code)
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

    /**
     * @param $websiteCode
     * @return array|mixed|string|null
     */
    public function getSourceCodeByWebsiteCode($websiteCode)
    {
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');
        $connection = $this->resourceConnection->getConnection();
        $query = $connection
            ->select()
            ->distinct()
            ->from($tableName, 'stock_id')
            ->where('code = ?', $websiteCode);

        $stockId = $connection->fetchCol($query);

        $sortOrder = $this->sortOrderBuilder
            ->setField(StockSourceLinkInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->addSortOrder($sortOrder)
            ->create();

        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            return [];
        }

        $assignedSources = [];
        foreach ($searchResult->getItems() as $link) {
            $assignedSources[] = $link->getSourceCode();
        }

        return $assignedSources[0];
    }

    /**
     * @param $mallId
     * @return int|\Magento\Store\Api\Data\StoreInterface
     */
    public function getStore($mallId)
    {
        $exactStore = 0;
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $configMallId = $this->getMallId('store', $store->getId());
            if ($mallId == $configMallId) {
                $exactStore = $store;
                break;
            }
        }
        return $exactStore;
    }

    /**
     * @param $product
     * @return false
     */
    public function sapIntegrationCheck($product)
    {
        if (is_null($product->getCustomAttribute(self::DISABLE_SAP_INTEGRATION))) {
            return false;
        } else {
            return $product->getCustomAttribute(self::DISABLE_SAP_INTEGRATION)->getValue();
        }
    }

    /**
     * Enable config to sum shipping amount into each item price when send SAP
     *
     * @param $storeId
     * @return mixed
     */
    public function getIsIncludeShippingAmountWhenSendRequest($storeId = null) {
        return $this->scopeConfig->getValue(
            self::INCLUDE_SHIPPING_AMOUNT_WHEN_SEND_REQUEST_ENABLE_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
