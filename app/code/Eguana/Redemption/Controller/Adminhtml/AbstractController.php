<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Abstract class for actions
 * abstract AbstractController
 */
abstract class AbstractController extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_Redemption::redemption';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * AbstractController constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * This method is used to init breadcrumbs
     *
     * @param $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu('Eguana_Redemption');
        $resultPage->addBreadcrumb(__('Redemption'), __('Manage'));
        $resultPage->addBreadcrumb(__('Manage Redemption'), __('Manage Redemption'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Redemption'));

        return $resultPage;
    }
}
