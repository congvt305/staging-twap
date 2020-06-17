<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:04 AM
 */

namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Controller\Adminhtml\AbstractController;
use Eguana\Magazine\Model\MagazineFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class Delete extends AbstractController
{
    /**
     * @var MagazineFactory
     */
    private $magazineFactory;

    /**
     * @var MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param MagazineFactory|null $magazineFactory
     * @param MagazineRepositoryInterface|null $magazineRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MagazineFactory $magazineFactory,
        MagazineRepositoryInterface $magazineRepository
    ) {
        $this->magazineFactory = $magazineFactory;
        $this->magazineRepository = $magazineRepository;
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
        $model = $this->magazineFactory->create();

        if ($id) {
            try {

                /** @var Magazine $model */

                $model = $this->magazineRepository->getById($id);
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
