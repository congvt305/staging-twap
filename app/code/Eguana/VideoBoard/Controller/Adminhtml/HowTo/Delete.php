<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 7:13 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Eguana\VideoBoard\Controller\Adminhtml\AbstractController;
use Eguana\VideoBoard\Model\VideoBoardFactory;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;

/**
 * This class is used to delete the video record
 *
 * Class Delete
 * Eguana\VideoBoard\Controller\Adminhtml\HowTo
 */
class Delete extends AbstractController
{
    /**
     * @var VideoBoardFactory
     */
    private $videoBoardFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param VideoBoardFactory|null $videoBoardFactory
     * @param VideoBoardRepositoryInterface|null $videoBoardRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        VideoBoardFactory $videoBoardFactory,
        VideoBoardRepositoryInterface $videoBoardRepository
    ) {
        $this->videoBoardFactory = $videoBoardFactory;
        $this->videoBoardRepository = $videoBoardRepository;
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    /**
     * execute the delete action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('entity_id');
        $model = $this->videoBoardFactory->create();

        if ($id) {
            try {
                /** @var VideoBoard $model */

                $model = $this->videoBoardRepository->getById($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Video was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Video could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
