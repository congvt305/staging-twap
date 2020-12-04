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
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote;
use Eguana\LinePay\Model\LinePay\Checkout;
use Magento\Framework\Session\Generic;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Eguana\LinePay\Model\LinePay\Checkout\Factory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class PlaceOrder
 *
 * Place Order LinePay
 */
class PlaceOrder extends AppAction implements
    HttpGetActionInterface,
    HttpPostActionInterface
{
    const LINE = 'line';

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var Quote
     */
    private $_quote;

    /**
     * @var Generic
     */
    private $linepaySession;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    protected $checkoutTypes = [];

    /**
     * @var Factory
     */
    protected $checkoutFactory;

    /**
     * @var
     */
    private $linePayCheckout;

    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param CartManagementInterface $quoteManagement
     * @param Generic $linepaySession
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param Factory $checkoutFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CartManagementInterface $quoteManagement,
        Generic $linepaySession,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        Factory $checkoutFactory,
        LoggerInterface $logger
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->linepaySession = $linepaySession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutFactory = $checkoutFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Place order
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $this->initCheckout();
            $this->checkout->place();
            $quoteId = $this->getQuote()->getId();
            $this->getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
            // an order may be created
            $order = $this->checkout->getOrder();
            if ($order) {
                $this->getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            }
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (LocalizedException $e) {
            $this->processException($e, $e->getRawMessage());
            return;
        } catch (\Exception $e) {
            $this->processException($e, 'We can\'t place the order.');
            return;
        }
    }

    /**
     * Initialize checkout data
     * @param CartInterface|null $quoteObject
     * @throws LocalizedException
     */
    private function initCheckout(CartInterface $quoteObject = null)
    {
        $quote = $quoteObject ? $quoteObject : $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new LocalizedException(__('We can\'t initialize LINEPay Checkout.'));
        }
        if (!(float)$quote->getGrandTotal()) {
            throw new LocalizedException(
                __(
                    'LINEPay can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
        }
        if (!isset($this->checkoutTypes[self::LINE])) {
            $parameters = [
                'params' => [
                    'quote' => $quote
                ],
            ];
            $this->checkoutTypes[self::LINE] = $this->checkoutFactory
                ->create(Checkout::class, $parameters);
        }
        $this->checkout = $this->checkoutTypes[self::LINE];
    }

    /**
     * Process line pay exceptions
     * @param \Exception $exception
     * @param string $message
     */
    private function processException(\Exception $exception, string $message)
    {
        $this->messageManager->addExceptionMessage($exception, __($message));
        $this->_redirect('checkout/onepage/failure');
    }

    /**
     * Get current quote
     * @return CartInterface|Quote
     */
    private function getQuote()
    {
        if (!$this->_quote) {
            try {
                if ($this->getSession()->getQuoteId()) {
                    $this->_quote = $this->quoteRepository->get($this->_getSession()->getQuoteId());
                    $this->getCheckoutSession()->replaceQuote($this->_quote);
                } else {
                    $this->_quote = $this->getCheckoutSession()->getQuote();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->_quote;
    }

    /**
     * Get checkout session
     * @return Session
     */
    private function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get session
     * @return Generic
     */
    private function getSession()
    {
        return $this->linepaySession;
    }
}
