<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 09:12 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * AbstractController class for actions
 */
abstract class AbstractController extends Action
{
    /**#@+
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_EventReservation::event_reservation';
    /**#@-*/

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

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
    protected function _init($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Event'), __('Reservation'))
            ->addBreadcrumb(__('Event Reservation'), __('Event Reservation'));

        return $resultPage;
    }
}
