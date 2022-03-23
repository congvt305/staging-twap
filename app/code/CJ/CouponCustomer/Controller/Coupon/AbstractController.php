<?php

namespace CJ\CouponCustomer\Controller\Coupon;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 *
 */
abstract class AbstractController extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * AbstractController constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
}
