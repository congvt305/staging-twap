<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: ali
 * Date: 9/2/21
 * Time: 1:12 PM
 */

namespace Eguana\EventReservation\Controller\Reservation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Success extends Action
{
    /**
     * Success constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * This method is used to load layout and render information
     *
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
