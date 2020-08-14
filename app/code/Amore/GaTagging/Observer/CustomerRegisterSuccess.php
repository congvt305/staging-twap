<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/21/20
 * Time: 10:29 AM
 */

namespace Amore\GaTagging\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Amore\GaTagging\Helper\Data $helper
    ) {

        $this->registry = $registry;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isActive()) {
            return $this;
        }
        $this->customerSession->setData('customer_register_success', true);
        return $this;
    }
}
