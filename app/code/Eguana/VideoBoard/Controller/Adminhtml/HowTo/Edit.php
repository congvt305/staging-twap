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

use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
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
    const ADMIN_RESOURCE = 'Eguana_VideoBoard::manage_videoboard';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        VideoBoardRepositoryInterface $videoBoardRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->videoBoardRepository = $videoBoardRepository;
        parent::__construct($context);
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_VideoBoard::videoboard')
            ->addBreadcrumb(__('Video'), __('Video'))
            ->addBreadcrumb(__('Manage Video Board'), __('Manage Video Board'));
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
