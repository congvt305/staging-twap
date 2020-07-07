<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 5:15 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Backend\App\Action;
use Eguana\EventManager\Model\EventManagerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException as LocalizedExceptionAlias;

/**
 * This Class is used to add new or update existing record
 *
 * Class Edit
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_EventManager::manage_event';
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepositoryInterface;

    /**
     * @var EventManagerFactory
     */
    private $eventManagerFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param EventManagerRepositoryInterface $eventManagerRepositoryInterface
     * @param EventManagerFactory $eventManagerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        EventManagerRepositoryInterface $eventManagerRepositoryInterface,
        EventManagerFactory $eventManagerFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->eventManagerRepositoryInterface = $eventManagerRepositoryInterface;
        $this->eventManagerFactory = $eventManagerFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * _initAction() method
     * This method is used to load layout, set active menu and breadcrumbs
     * @return Page
     */

    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_EventManager::event')
            ->addBreadcrumb(__('Event'), __('Event'))
            ->addBreadcrumb(__('Manage Event'), __('Manage Event'));
        return $resultPage;
    }

    /**
     * Edit CMS page
     * @return Page|ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $id?$this->eventManagerRepositoryInterface->getById($id):null;

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('eguana_event_manager', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Event') : __('New Event'),
            $id ? __('Edit Event') : __('New Event')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? $model->getTitle() : __('New Event'));
        return $resultPage;
    }
}
