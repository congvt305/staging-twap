<?php

namespace CJ\Payoo\Plugin\Payment;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class Index
 */
class Index
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \CJ\Payoo\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \CJ\Payoo\Logger\Logger $logger
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session             $checkoutSession,
        \CJ\Payoo\Logger\Logger                     $logger,
        \Magento\Sales\Model\OrderFactory           $orderFactory,
        \Magento\Quote\Model\QuoteRepository        $quoteRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }


    /**
     * @param \Payoo\PayNow\Controller\Payment\Index $controllerAction
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\ResultInterface|mixed
     */
    public function aroundExecute(\Payoo\PayNow\Controller\Payment\Index $controllerAction, \Closure $proceed)
    {
        $orderId = $this->checkoutSession->getLastRealOrderId();
        if ($orderId) {
            return $proceed();
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            try {
                $order = $this->_getLastRealOrder();
                $payment = $order->getPayment();
                $response = $payment->getAdditionalInformation('RESULT');
                $resultRedirect->setUrl($response['order']['payment_url']);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
                $this->logger->critical(json_encode([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]));
                $this->checkoutSession->restoreQuote();
                $resultRedirect->setUrl('/checkout/cart');
            }

            return $resultRedirect;
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _getLastRealOrder(): \Magento\Sales\Model\Order
    {
        $order = $this->orderFactory->create();
        $orderId = $this->_getLastRealOrderId();
        if ($orderId) {
            $order->load($orderId);
        }
        return $order;
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function _getLastRealOrderId(): int
    {
        $orderId = null;
        $lastQuoteId = $this->checkoutSession->getLastQuoteId();
        if ($lastQuoteId) {
            $reservedOrderId = $this->quoteRepository->get($lastQuoteId)->getReservedOrderId();
            $orderModel = $this->orderFactory->create();
            $order = $orderModel->loadByIncrementId($reservedOrderId);
            $orderId = (int)$order->getId();
        }
        if (!$orderId) {
            throw new \Exception("ERROR CANNOT GET LAST ORDER ID");
        }
        return $orderId;
    }
}
