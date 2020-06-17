<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 10
 * Time: 오후 12:20
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Amore\CustomerRegistration\Model\POSSystem;
use \Magento\Customer\Model\Data\Customer;

/**
 * To handle the customer registeration
 * Class RegisterSuccess
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class RegisterSuccess implements ObserverInterface
{

    /**
     * @var POSSystem
     */
    private $POSSystem;

    public function __construct(
        POSSystem $POSSystem
    ) {
        $this->POSSystem = $POSSystem;
    }

    /**
     * Observer called on successfull customer registration
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /**
         * @var Customer $customer
         */
        $customer = $observer->getEvent()->getData('customer');
        $this->POSSystem->syncMember($customer, 'register');

    }
}