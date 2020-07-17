<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 8:22 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Eguana\EventManager\Controller\Adminhtml\AbstractController;
use Eguana\EventManager\Model\EventManagerFactory;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * This class is used to delete the event record
 *
 * Class Delete
 */
class Delete extends AbstractController
{
    /**
     * @var EventManagerFactory
     */
    private $eventManagerFactory;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * Delete constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EventManagerFactory|null $eventManagerFactory
     * @param EventManagerRepositoryInterface|null $eventManagerRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventManagerFactory $eventManagerFactory,
        EventManagerRepositoryInterface $eventManagerRepository
    ) {
        $this->eventManagerFactory = $eventManagerFactory;
        $this->eventManagerRepository = $eventManagerRepository;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * execute the delete action
     * @return ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('entity_id');
        $model = $this->eventManagerFactory->create();

        if ($id) {
            try {
                /** @var EventManager $model */
                $model = $this->eventManagerRepository->getById($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Event was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Event could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
