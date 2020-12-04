<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;

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
    const ADMIN_RESOURCE = 'Eguana_Redemption::redemption';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepositoryInterface;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RedemptionRepositoryInterface $redemptionRepositoryInterface
     * @param DataPersistorInterface $dataPersistor
     * @param RedirectFactory $resultRedirectFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RedemptionRepositoryInterface $redemptionRepositoryInterface,
        DataPersistorInterface $dataPersistor,
        RedirectFactory $resultRedirectFactory,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->redemptionRepositoryInterface = $redemptionRepositoryInterface;
        $this->dataPersistor = $dataPersistor;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Edit Redemption
     *
     * @return Page|ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model and breadcrumbs
        $id = $this->getRequest()->getParam('redemption_id');
        $this->dataPersistor->set('current_redemption_id', $id);
        if (isset($id)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $checkData = $this->redemptionRepositoryInterface->getById($id);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            if (!$checkData->getId()) {
                return $resultRedirect->setPath('*/*/');
            }
        }
        $model = $id ? $this->redemptionRepositoryInterface->getById($id) : null;
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_Redemption::redemption')
            ->addBreadcrumb(__('Redemption'), __('Redemption'))
            ->addBreadcrumb(__('Manage Redemption'), __('Manage Redemption'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Redemption') : __('New Redemption'),
            $id ? __('Edit Redemption') : __('New Redemption')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Redemptions'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? $model->getTitle() : __('New Redemption'));
        return $resultPage;
    }
}
