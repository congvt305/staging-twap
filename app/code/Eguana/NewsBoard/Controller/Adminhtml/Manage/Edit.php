<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 5:15 PM
 */
namespace Eguana\NewsBoard\Controller\Adminhtml\Manage;

use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action;

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
    const ADMIN_RESOURCE = 'Eguana_NewsBoard::manage_news';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param NewsRepositoryInterface $newsRepositoryInterface
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        NewsRepositoryInterface $newsRepositoryInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->newsRepositoryInterface = $newsRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Edit CMS page
     * @return Page|ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        // 1. Get ID and create model and breadcrumbs
        $id = $this->getRequest()->getParam('news_id');
        if (isset($id)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $this->newsRepositoryInterface->getById($id);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $model = $id?$this->newsRepositoryInterface->getById($id):null;
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_NewsBoard::news')
            ->addBreadcrumb(__('News'), __('News'))
            ->addBreadcrumb(__('Manage News'), __('Manage News'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit News') : __('New News'),
            $id ? __('Edit News') : __('New News')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('News'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? $model->getTitle() : __('New News'));
        return $resultPage;
    }
}
