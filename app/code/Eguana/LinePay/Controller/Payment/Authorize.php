<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 23/9/20
 * Time: 12:56 PM
 */
namespace Eguana\LinePay\Controller\Payment;

use Eguana\LinePay\Model\Payment as LinepayPayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\PageFactory;
use Eguana\LinePay\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Authorize
 *
 * Authorize payment request
 */
class Authorize extends Action
{
    /**
     * @var LinepayPayment
     */
    private $linepayPayment;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Authorize constructor.
     * @param Context $context
     * @param LinepayPayment $linepayPayment
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        LinepayPayment $linepayPayment,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->linepayPayment                     = $linepayPayment;
        $this->resultPageFactory                  = $resultPageFactory;
        $this->helper                             = $helper;
    }

    /**
     * Authorize payment
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $resultPage = $this->resultRedirectFactory->create();
            $transactionId = $this->getRequest()->getParam('transactionId');
            $orderId = $this->getRequest()->getParam('orderId');
        } catch (\Exception $e) {
            throw $e;
        }
        if ($this->helper->isMobile()) {
            $this->_forward('placeOrder');
        } else {
            $this->closePopUpWindow($this);
        }
    }

    /**
     * Close pop up window
     * @param $objectAction
     */
    private function closePopUpWindow($objectAction)
    {
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(Template::class)
            ->setTemplate('Eguana_LinePay::close-popup.phtml')
            ->toHtml();
        $objectAction->getResponse()->setBody($block);
    }
}
