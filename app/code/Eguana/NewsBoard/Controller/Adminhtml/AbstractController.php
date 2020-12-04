<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:40 PM
 */

namespace Eguana\NewsBoard\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * Abstract class for actions
 * abstract AbstractController
 */
abstract class AbstractController extends Action
{
    /**#@+
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_NewsBoard::manage_news';
    /**#@-*/

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
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
        $resultPage->setActiveMenu('Eguana_NewsBoard');
        $resultPage->addBreadcrumb(__('News'), __('Board'));
        $resultPage->addBreadcrumb(__('Manage News'), __('Manage News'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage News'));

        return $resultPage;
    }
}
