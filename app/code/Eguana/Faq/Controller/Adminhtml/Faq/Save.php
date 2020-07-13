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

use Eguana\Faq\Model\Faq;
use Magento\Backend\App\Action\Context;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Eguana\Faq\Model\FaqFactory;
use Magento\Backend\Model\View\Result\Redirect as RedirectAlias;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Eguana\Faq\Controller\Adminhtml\AbstractController;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Eguana\Faq\Api\Data\FaqInterface;

/**
 * This Class is used to save FAQ information
 * Class Save
 */
class Save extends AbstractController
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var FaqFactory
     */
    private $faqFactory;

    /**
     * @var FaqRepositoryInterface
     */
    private $faqRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param FaqFactory|null $faqFactory
     * @param FaqRepositoryInterface|null $faqRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        FaqFactory $faqFactory,
        FaqRepositoryInterface $faqRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->faqFactory = $faqFactory;
        $this->faqRepository = $faqRepository;

        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    /**
     * Save action
     *
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|mixed
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            /** @var Faq $model */
            $model = $this->faqFactory->create();

            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                try {
                    $model = $this->faqRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                    return $this->processResultRedirect($model, $resultRedirect, $data);
                }
            }

            $model->setData($data);

            try {
                $this->faqRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the block.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('eguana_faq', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
    }
    /**
     * Process result redirect
     *
     * @param FaqInterface $model
     * @param RedirectAlias $resultRedirect
     * @param $model
     * @param $resultRedirect
     * @param $data
     * @return mixed
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newFaq = $this->faqFactory->create(['data' => $data]);
            $newFaq->setId(null);
            $newFaq->setIsActive(false);
            $newFaq->setStoreId($model->getStoreId());
            $this->faqRepository->save($newFaq);
            $this->messageManager->addSuccessMessage(__('You duplicated the faq.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'entity_id' => $newFaq->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_faq');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
