<?php

namespace CJ\CustomCustomer\Cron;

/**
 * Class SynPosCstmNO
 */
class SynPosCstmNO
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \CJ\CustomCustomer\Model\PosIntegration
     */
    protected $posIntg;

    /**
     * @var \CJ\CustomCustomer\Helper\Data
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \CJ\CustomCustomer\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CJ\CustomCustomer\Model\PosIntegration $posIntg
     * @param \CJ\CustomCustomer\Helper\Data $config
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface                       $storeManager,
        \CJ\CustomCustomer\Model\PosIntegration                          $posIntg,
        \CJ\CustomCustomer\Helper\Data                                   $config,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \CJ\CustomCustomer\Logger\Logger                                 $logger
    ) {
        $this->storeManager = $storeManager;
        $this->posIntg = $posIntg;
        $this->config = $config;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $customers = $this->_getCustomers($store->getWebsiteId());
            foreach ($customers as $customer) {
                /** @var \Magento\Customer\Model\Customer $customer */
                try {
                    $cstmId = $customer->getId();
                    $posCstmNO = $this->posIntg->synPosCstmNO($customer);
                    $this->logger->info(__("Pos cstmno %1 for customer #%2 sync successful.", $posCstmNO, $cstmId));
                } catch (\Exception $e) {
                    $this->logger->error(__("Pos cstmno sync failed for customer #%1.", $cstmId));
                    $this->logger->error($e->getMessage(), ['id' => $cstmId]);
                }
            }
        }
        return $this;
    }

    /**
     * @param $websiteId
     * @return array
     */
    protected function _getCustomers($websiteId): array
    {
        $collection = $this->customerCollectionFactory->create()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('pos_cstm_no', ['is' => new \Zend_Db_Expr('null')])
            ->setPageSize($this->config->getPosCstmNOLimit($websiteId));
        return $collection->getItems();
    }
}
