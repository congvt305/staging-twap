<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/6/20
 * Time: 12:49 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * This class is used to show the Grid for Video Board Record in Admin Panel
 *
 * Class Index
 * Eguana\VideoBoard\Controller\Adminhtml\HowTo
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Video Board')));
        return $resultPage;
    }
}
