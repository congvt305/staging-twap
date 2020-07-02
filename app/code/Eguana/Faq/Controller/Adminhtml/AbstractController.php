<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class AbstractController
 * @package Eguana\Faq\Controller\Adminhtml
 */
abstract class AbstractController extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * initialize
     * @param $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu('Eguana_Faq');
        $resultPage->addBreadcrumb(__('FAQ'), __('FAQ'));
        $resultPage->addBreadcrumb(__('Manage FAQ'), __('Manage FAQ'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage FAQ'));

        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eguana_Faq::manage_faq');
    }
}
