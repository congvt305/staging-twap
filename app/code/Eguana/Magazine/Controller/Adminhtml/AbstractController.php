<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 5:07 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ContextAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\PageFactory as PageFactoryAlias;

/**
 * Abstract class for actions
 * abstract AbstractController
 */

abstract class AbstractController extends Action
{
    /**
     * @var PageFactoryAlias
     */
    protected $resultPageFactory;

    /**
     * @param ContextAlias $context
     * @param PageFactoryAlias $resultPageFactory
     */
    public function __construct(
        ContextAlias $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @param $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu('Eguana_Magazine');
        $resultPage->addBreadcrumb(__('Magazine'), __('Magazine'));
        $resultPage->addBreadcrumb(__('Manage Magazine'), __('Manage Magazine'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Magazine'));

        return $resultPage;
    }

    /**
     * Check the permission to run it
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eguana_Magazine::manage_magazine');
    }
}
