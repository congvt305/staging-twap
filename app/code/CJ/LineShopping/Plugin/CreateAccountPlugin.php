<?php

namespace CJ\LineShopping\Plugin;

use Amore\CustomerRegistration\Plugin\CreateCustomer;
use Magento\Framework\Webapi\Rest\Request;
use CJ\LineShopping\Logger\Logger;
use CJ\LineShopping\Model\Rest\Api as LineShoppingApi;
use Magento\Customer\Model\AccountManagementApi;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class CreateAccountPlugin
{
    const RESET_PASSWORD_PATH = 'customer/account/createPassword/?token=';
    /**
     * @var LineShoppingApi
     */
    protected LineShoppingApi $lineShoppingApi;

    /**
     * @var Request
     */
    protected Request $requestApi;

    /**
     * @var CustomerRegistry
     */
    protected CustomerRegistry $customerRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerRegistry $customerRegistry
     * @param LineShoppingApi $lineShoppingApi
     * @param Request $requestApi
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerRegistry $customerRegistry,
        LineShoppingApi $lineShoppingApi,
        Request $requestApi,
        Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->lineShoppingApi = $lineShoppingApi;
        $this->requestApi = $requestApi;
        $this->logger = $logger;
    }

    /**
     * @param AccountManagementApi $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterCreateAccount(AccountManagementApi $subject, CustomerInterface $customer)
    {
        try {
            $customerRegistry = $this->customerRegistry->retrieve($customer->getId());
            $baseUrl = $this->storeManager->getStore($customer->getStoreId())->getBaseUrl();
            if ($this->isPOSRequest()) {
                $lineId = $customer->getCustomAttribute('line_id') ? $customer->getCustomAttribute('line_id')->getValue() : '';
                $resetPasswordUrl = $baseUrl . self::RESET_PASSWORD_PATH . $customerRegistry->getRpToken();
                $websiteId = $customer->getWebsiteId();
                if ($lineId) {
                    $this->lineShoppingApi->sendMessageToLine($lineId, $resetPasswordUrl, $websiteId);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->addError(Logger::LINE_CUSTOMER,
                [
                    'email' => $customer->getEmail(),
                    'lineId' => $lineId,
                    'message' => $exception->getMessage()
                ]
            );
        }
        return $customer;
    }

    /**
     * check POS request
     *
     * @return bool
     */
    private function isPOSRequest()
    {
        $data = $this->requestApi->getRequestData();
        if ($data && isset($data[CreateCustomer::IS_POS]) && $data[CreateCustomer::IS_POS] == 1) {
            return true;
        }
        return false;
    }
}
