<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Controller\Adminhtml\Faq;

use Eguana\Faq\Controller\Adminhtml\AbstractController;
use Eguana\Faq\Model\FaqFactory;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 *
 * Eguana\Faq\Controller\Adminhtml\Faq
 */
class Delete extends AbstractController
{
    /**
     * @var FaqFactory
     */
    private $faqFactory;

    /**
     * @var FaqRepositoryInterface
     */
    private $faqRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param FaqFactory|null $faqFactory
     * @param FaqRepositoryInterface|null $faqRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        FaqFactory $faqFactory,
        FaqRepositoryInterface $faqRepository
    ) {
        $this->faqFactory = $faqFactory;
        $this->faqRepository = $faqRepository;
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    /**
     * Execute method
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('entity_id');
        $model = $this->faqFactory->create();

        if ($id) {
            try {
                /** @var Faq $model */
                $model = $this->faqRepository->getById($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('row was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('row could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
