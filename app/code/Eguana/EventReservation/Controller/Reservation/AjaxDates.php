<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 3/11/20
 * Time: 6:10 PM
 */
namespace Eguana\EventReservation\Controller\Reservation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Used to get available dates across counter id
 *
 * Class AjaxDates
 */
class AjaxDates extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        ResultFactory $resultFactory
    ) {
        $this->resultFactory        = $resultFactory;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultJsonFactory    = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Method used to load layout and render information
     *
     * @return ResponseInterface|Json|ResultInterface|Layout
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            $resultPage = $this->resultPageFactory->create();
            $result = $this->resultJsonFactory->create();
            $response = $resultPage->getLayout()
                ->getBlock('reservation.ajax.dates')
                ->toHtml();
            return $result->setData(['output' => $response]);
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl('/');
        }
    }
}
