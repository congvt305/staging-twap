<?php
namespace Sapt\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class History extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        return $this->pageFactory->create();
    }

}
