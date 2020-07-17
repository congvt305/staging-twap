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

use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action;

/**
 * This Class is used to add new or update existing record
 *
 * Class Edit
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_EventManager::manage_event';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepositoryInterface;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EventManagerRepositoryInterface $eventManagerRepositoryInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventManagerRepositoryInterface $eventManagerRepositoryInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->eventManagerRepositoryInterface = $eventManagerRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Edit CMS page
     * @return Page|ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        // 1. Get ID and create model and breadcrumbs
        $id = $this->getRequest()->getParam('entity_id');
        $model = $id?$this->eventManagerRepositoryInterface->getById($id):null;
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_EventManager::event')
            ->addBreadcrumb(__('Event'), __('Event'))
            ->addBreadcrumb(__('Manage Event'), __('Manage Event'));
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
