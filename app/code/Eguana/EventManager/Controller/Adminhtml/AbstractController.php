<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 7:40 PM
 */

namespace Eguana\EventManager\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * Abstract class for actions
 * abstract AbstractController
 */
abstract class AbstractController extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

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
     * this method is used to init breadcrumbs
     * @param $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu('Eguana_EventManager');
        $resultPage->addBreadcrumb(__('Event'), __('Manager'));
        $resultPage->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Events'));

        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eguana_EventManager::manage_event');
    }
}
