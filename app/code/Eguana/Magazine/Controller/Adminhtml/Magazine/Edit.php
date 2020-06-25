<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:10 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Action for Edit Button
 * Class Edit
 */
class Edit extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_Magazine::config_stores';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Eguana\Magazine\Api\MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Eguana\Magazine\Api\MagazineRepositoryInterface $magazineRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->magazineRepository = $magazineRepository;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Cms::cms_page')
            ->addBreadcrumb(__('Magazine'), __('Magazine'))
            ->addBreadcrumb(__('Manage Magazines'), __('Manage Magazines'));
        return $resultPage;
    }
    /**
     * Edit CMS page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $id ? $this->magazineRepository->getById($id) : null;
        if ($id) {
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This magazine no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('eguana_magazine', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Magazine') : __('New Magazine'),
            $id ? __('Edit Magazine') : __('New Magazine')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Magazines'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? $model->getTitle() : __('New Magazine'));

        return $resultPage;
    }
}
