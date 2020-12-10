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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param LinepayPayment $linepayPayment
     * @param PageFactory $resultPageFactory
     * @param CheckoutSession $checkoutSession
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        LinepayPayment $linepayPayment,
        PageFactory $resultPageFactory,
        CheckoutSession $checkoutSession,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->linepayPayment                     = $linepayPayment;
        $this->resultPageFactory                  = $resultPageFactory;
        $this->checkoutSession                    = $checkoutSession;
        $this->serializer                         = $serializer;
    }

    /**
     * Get redirect url
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $result = $this->linepayPayment->getRedirectUrl();
            $data = $this->getRequest()->getParam('data');
            if ($data) {
                $data = $this->serializer->unserialize($data);
                $additionalData = $data["additional_data"];
                $additionalData["method_title"] = $data["method"];
                $quote = $this->checkoutSession->getQuote();
                $quote->getPayment()->setAdditionalInformation(
                    'raw_details_info',
                    $additionalData
                );
                $quote->getPayment()->save();
            }
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
