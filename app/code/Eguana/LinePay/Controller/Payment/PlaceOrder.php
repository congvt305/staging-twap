<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 11/11/20
 * Time: 7:39 PM
 */
namespace Eguana\LinePay\Controller\Payment;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class PlaceOrder
 *
 * Line pay place order
 */
class PlaceOrder extends AppAction implements
    HttpGetActionInterface,
    HttpPostActionInterface
{

    /**
     * @var
     */
    private $_order;

    /**
     * @var
     */
    private $_quote;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $linepaySession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $quoteManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PlaceOrder constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param \Magento\Framework\Session\Generic $linepaySession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Session\Generic $linepaySession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->linepaySession = $linepaySession;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Place Order
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $this->_quote = $this->_checkoutSession->getQuote();
            $this->_quote->collectTotals();
            $order = $this->quoteManagement->submit($this->_quote);
            if (!$order) {
                return;
            }
            switch ($order->getState()) {
                case \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT:
                    break;
                case \Magento\Sales\Model\Order::STATE_PROCESSING:
                case \Magento\Sales\Model\Order::STATE_COMPLETE:
                case \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW:
                    try {
                        if (!$order->getEmailSent()) {
                            $this->orderSender->send($order);
                        }
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                    $this->_checkoutSession->start();
                    break;
                default:
                    break;
            }
            $this->_order = $order;
            $quoteId = $this->_getQuote()->getId();
            $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->getOrder();
            if ($order) {
                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            }
            $this->_redirect('checkout/onepage/success');
        } catch (LocalizedException $e) {
            $this->processException($e, $e->getRawMessage());
        } catch (\Exception $e) {
            $this->processException($e, 'We can\'t place the order.');
        }
    }

    /**
     * Get order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Process Exception
     * @param \Exception $exception
     * @param string $message
     */
    private function processException(\Exception $exception, string $message)
    {
        $this->messageManager->addExceptionMessage($exception, __($message));
        $this->_redirect('checkout/onepage/failure');
    }

    /**
     * Get Quote
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            try {
                if ($this->_getSession()->getQuoteId()) {
                    $this->_quote = $this->quoteRepository->get($this->_getSession()->getQuoteId());
                    $this->_getCheckoutSession()->replaceQuote($this->_quote);
                } else {
                    $this->_quote = $this->_getCheckoutSession()->getQuote();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->_quote;
    }

    /**
     * Get checkout session
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    private function prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Get session
     * @return \Magento\Framework\Session\Generic
     */
    private function _getSession()
    {
        return $this->linepaySession;
    }
}
