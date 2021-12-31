<?php

namespace CJ\CouponCustomer\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;

class Data extends AbstractHelper
{

    protected Session $customerSession;


    public function __construct(Context $context,
                                Session $customerSession
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    public function getCustomerId() {

        return $this->customerSession->getCustomerGroupId();
    }
}
