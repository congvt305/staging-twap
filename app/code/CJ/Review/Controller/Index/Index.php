<?php

namespace CJ\Review\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Index extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        } else {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        }
    }
}
