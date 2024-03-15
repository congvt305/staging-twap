<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 4
 * Time: 오전 11:02
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Amore\CustomerRegistration\Model\POSLogger;
use Amore\CustomerRegistration\Model\POSSystem;
use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSyncAPI;

/**
 * To sync with POS on customer delete
 * Class DeleteSuccess
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class DeleteSuccess implements ObserverInterface
{
    /**
     * @var POSSystem
     */
    private $POSSystem;

    /**
     * @var POSLogger
     */
    private $logger;
    /**
     * @var POSSyncAPI
     */
    private $posSyncAPI;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;


    /**
     * @param POSLogger $logger
     * @param POSSystem $POSSystem
     * @param POSSyncAPI $posSyncAPI
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        POSLogger $logger,
        POSSystem $POSSystem,
        POSSyncAPI $posSyncAPI,
        \Magento\Framework\App\State $state
    ) {
        $this->POSSystem = $POSSystem;
        $this->logger = $logger;
        $this->posSyncAPI = $posSyncAPI;
        $this->state = $state;
    }

    /**
     * Observer called on successfull customer deletion
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getEvent()->getCustomer();
            $customerDefaultBillingAddress = null;
            $defaultBillingAddressId = $customer->getDefaultBilling();
            if ($defaultBillingAddressId) {
                $addresses = $customer->getAddresses();
                foreach ($addresses as $address) {
                    if ($address->getId() == $defaultBillingAddressId) {
                        $customerDefaultBillingAddress = $address;
                        break;
                    }
                }
            }
            if ($this->state->getAreaCode() != 'webapi_rest') {
                $APIParameters = $this->posSyncAPI->getAPIParameters($customer, $customerDefaultBillingAddress, 'delete');
                $this->POSSystem->syncMember($APIParameters, $customer->getStoreId());
            }
        } catch (\Exception $e) {
            $this->logger->addAPILog($e->getMessage());
        }
    }
}
