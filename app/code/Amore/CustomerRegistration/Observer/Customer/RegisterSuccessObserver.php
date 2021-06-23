<?php
/**
 * Created by PhpStorm
 * User: Phat Pham
 * Date:  23.06.2021
 */

namespace Amore\CustomerRegistration\Observer\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RegisterSuccessObserver
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class RegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * RegisterSuccessObserver constructor.
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    )
    {
        $this->session = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $this->session->setEventRegisterSuccess(1);
    }
}
