<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 1:25 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\Page as PageAlias;
use Magento\Framework\View\Result\PageFactory;

/**
 * This class is used to show the Grid for Events Record in Admin Panel
 *
 * Class Index
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory = false;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * execute() Method
     * This method is used to create new page and add title on the Grid page
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|PageAlias
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Events')));
        return $resultPage;
    }
}
