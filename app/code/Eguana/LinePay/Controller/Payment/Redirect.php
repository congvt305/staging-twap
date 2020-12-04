<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 7/9/20
 * Time: 6:34 PM
 */
namespace Eguana\LinePay\Controller\Payment;

use Eguana\LinePay\Model\Payment as LinepayPayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Element\Template;

/**
 * Class Redirect
 *
 * Redirect to payment page
 */
class Redirect extends Action implements HttpGetActionInterface
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
     * Redirect constructor.
     * @param Context $context
     * @param LinepayPayment $linepayPayment
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        LinepayPayment $linepayPayment,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->linepayPayment                     = $linepayPayment;
        $this->resultPageFactory                  = $resultPageFactory;
    }

    /**
     * Get redirect url
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $result = $this->linepayPayment->getRedirectUrl();
            if (!($result['status'] === 'Failure')) {
                $this->_redirect($result['url']);
            }
            if ($result['status'] === 'Failure') {
                $this->getResponse()->setBody($result['msg']);
            }
        } catch (\Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }
}
