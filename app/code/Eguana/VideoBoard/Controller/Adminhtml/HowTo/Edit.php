<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/6/20
 * Time: 8:06 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Backend\App\Action;
use Eguana\VideoBoard\Model\VideoBoardFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * This Class is used to add new or update existing record
 *
 * Class Edit
 * Eguana\VideoBoard\Controller\Adminhtml\HowTo
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_VideoBoard::manage_videoboard';
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * @var VideoBoardFactory
     */
    private $videoBoardFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param VideoBoardFactory $videoBoardFactory
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        VideoBoardRepositoryInterface $videoBoardRepository,
        VideoBoardFactory $videoBoardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->videoBoardRepository = $videoBoardRepository;
        $this->coreRegistry = $registry;
        $this->videoBoardFactory = $videoBoardFactory;
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
        $resultPage->setActiveMenu('Eguana_VideoBoard::videoboard')
            ->addBreadcrumb(__('Video'), __('Video'))
            ->addBreadcrumb(__('Manage Video Board'), __('Manage Video Board'));
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
        $model = $id?$this->videoBoardRepository->getById($id):null;

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This video no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('eguana_video_board', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Video') : __('New Video'),
            $id ? __('Edit Video') : __('New Video')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Videos'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? $model->getTitle() : __('New Video'));
        return $resultPage;
    }
}
