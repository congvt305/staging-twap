<?php
namespace Sapt\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class FindPasswordComplete extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $session
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->session = $session;
    }

    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        return $this->pageFactory->create();
    }

}
