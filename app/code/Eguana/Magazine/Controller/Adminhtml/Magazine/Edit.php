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

use Eguana\Magazine\Api\MagazineRepositoryInterface as MagazineRepositoryInterfaceAlias;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page as PageAlias;
use Magento\Backend\Model\View\Result\Redirect as RedirectAlias;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias1;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory as PageFactoryAlias;
use Magento\Backend\App\Action\Context;

/**
 * Action for Edit Button
 * Class Edit
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_Magazine::manage_magazine';

    /**
     * @var PageFactoryAlias
     */
    private $resultPageFactory;
    /**
     * @var MagazineRepositoryInterfaceAlias
     */
    private $magazineRepository;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactoryAlias $resultPageFactory
     * @param RegistryAlias $registry
     * @param MagazineRepositoryInterfaceAlias $magazineRepository
     */
    public function __construct(
        Context $context,
        PageFactoryAlias $resultPageFactory,
        MagazineRepositoryInterfaceAlias $magazineRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->magazineRepository = $magazineRepository;
        parent::__construct($context);
    }

    /**
     * Edit CMS Page
     * @return PageAlias|ResponseInterfaceAlias|RedirectAlias1|ResultInterfaceAlias
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->magazineRepository->getById($id);
        $resultPage = $this->resultPageFactory->create();
        if ($id) {
            $resultPage->addBreadcrumb(__('Edit Magazine'), __('Edit Magazine'));
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Magazine'));
        } else {
            $resultPage->addBreadcrumb(__('New Magazine'), __('New Magazine'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Magazine'));
        }

        return $resultPage;
    }
}
