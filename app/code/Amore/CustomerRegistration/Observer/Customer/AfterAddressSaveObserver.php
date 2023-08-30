<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 11
 * Time: 오후 1:40
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Amore\CustomerRegistration\Model\POSLogger;
use Amore\CustomerRegistration\Model\POSSystem;
use Amore\CustomerRegistration\Plugin\CreateCustomer;
use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSyncAPI;
use Magento\Framework\App\RequestInterface;

/**
 * To communicate with the POS on customer address change
 * Class AfterAddressSaveObserver
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class AfterAddressSaveObserver implements ObserverInterface
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $requestApi;

    public function __construct(
        RequestInterface $request,
        POSLogger $logger,
        POSSystem $POSSystem,
        POSSyncAPI $posSyncAPI,
        \Magento\Framework\Webapi\Rest\Request $requestApi,
    ) {
        $this->POSSystem = $POSSystem;
        $this->logger = $logger;
        $this->posSyncAPI = $posSyncAPI;
        $this->request = $request;
        $this->requestApi = $requestApi;
    }

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            $actionName = $this->request->getActionName();
            if ($actionName != 'editPost' && $actionName != 'createpost') {
                /** @var \Magento\Customer\Model\Address $address */
                $address = $observer->getData('customer_address');
                if ($address->getIsDefaultBilling()) {
                    $customer = $address->getCustomer();
                    if (!$this->isPOSRequest()) {
                        $APIParameters = $this->posSyncAPI->getAPIParameters($customer, $address, 'update');
                        $this->POSSystem->syncMember($APIParameters, $customer->getStoreId());
                    }

                }
            }
        } catch (\Exception $e) {
            $this->logger->addAPILog($e->getMessage());
        }

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
