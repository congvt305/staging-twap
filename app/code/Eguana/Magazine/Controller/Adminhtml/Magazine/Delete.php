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
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory as PageFactoryAlias;
use Psr\Log\LoggerInterface;

/**
 * Action for delete button
 * Class Delete
 */
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
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * Delete constructor.
     * @param Context $context
     * @param PageFactoryAlias $resultPageFactory
     * @param MagazineFactory|null $magazineFactory
     * @param MagazineRepositoryInterface|null $magazineRepository
     */
    public function __construct(
        Context $context,
        PageFactoryAlias $resultPageFactory,
        MagazineFactory $magazineFactory,
        MagazineRepositoryInterface $magazineRepository,
        LoggerInterface $logger
    ) {
        $this->magazineFactory = $magazineFactory;
        $this->magazineRepository = $magazineRepository;
        parent::__construct($context, $resultPageFactory);
        $this->logger = $logger;
    }

    /**
     * execute the delete action
     * @return ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
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
                $this->logger->debug($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('row could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
