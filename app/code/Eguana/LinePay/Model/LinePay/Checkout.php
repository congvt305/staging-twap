<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 30/11/20
 * Time: 4:55 PM
 */
namespace Eguana\LinePay\Model\LinePay;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Quote\Model\Quote;
use Magento\Customer\Model\Session;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartManagementInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Group;

/**
 * Class Checkout
 *
 * Place order
 */
class Checkout
{
    /**
     * @var Quote
     */
    private $_quote;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $checkoutData;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Order
     *
     * @var Order
     */
    protected $_order;

    /**
     * Checkout constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $customerSession
     * @param Data $checkoutData
     * @param CheckoutSession $checkoutSession
     * @param CartManagementInterface $quoteManagement
     * @param OrderSender $orderSender
     * @param LoggerInterface $logger
     * @param array $params
     * @throws \Exception
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Session $customerSession,
        Data $checkoutData,
        CheckoutSession $checkoutSession,
        CartManagementInterface $quoteManagement,
        OrderSender $orderSender,
        LoggerInterface $logger,
        $params = []
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->checkoutData = $checkoutData;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
        if (isset($params['quote']) && $params['quote'] instanceof Quote) {
            $this->_quote = $params['quote'];
        } else {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(__('Quote instance is required.'));
        }
    }

    /**
     * Place order
     * @param null $shippingMethodCode
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function place($shippingMethodCode = null)
    {
        if ($shippingMethodCode) {
            $this->updateShippingMethod($shippingMethodCode);
        }
        if ($this->getCheckoutMethod() == Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->ignoreAddressValidation();

        $this->_quote = $this->checkoutSession->getQuote();
        $this->_quote->collectTotals();
        $order = $this->quoteManagement->submit($this->_quote);
        if (!$order) {
            return;
        }
        switch ($order->getState()) {
            case Order::STATE_PENDING_PAYMENT:
                break;
            case Order::STATE_PROCESSING:
            case Order::STATE_COMPLETE:
            case Order::STATE_PAYMENT_REVIEW:
                try {
                    if (!$order->getEmailSent()) {
                        $this->orderSender->send($order);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
                $this->checkoutSession->start();
                break;
            default:
                break;
        }
        $this->_order = $order;
    }

    /**
     * Get last placed order
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Ignore address validation if required
     */
    private function ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            $this->_quote->getBillingAddress()->setSameAsBilling(1);
        }
    }

    /**
     * Get customer session
     * @return Session
     */
    private function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get checkout method
     * @return string
     */
    private function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$this->_quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->_quote)) {
                $this->_quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $this->_quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }
        return $this->_quote->getCheckoutMethod();
    }

    /**
     * Prepare guest quote
     * @return $this
     */
    private function prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
        if ($quote->getCustomerFirstname() === null && $quote->getCustomerLastname() === null) {
            $quote->setCustomerFirstname($quote->getBillingAddress()->getFirstname());
            $quote->setCustomerLastname($quote->getBillingAddress()->getLastname());
            if ($quote->getBillingAddress()->getMiddlename() === null) {
                $quote->setCustomerMiddlename($quote->getBillingAddress()->getMiddlename());
            }
        }
        return $this;
    }

    /**
     * Update shipping method based on current quote
     * @param $methodCode
     */
    private function updateShippingMethod($methodCode)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if (!$this->_quote->getIsVirtual() && $shippingAddress) {
            if ($methodCode != $shippingAddress->getShippingMethod()) {
                $this->ignoreAddressValidation();
                $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);
                $cartExtension = $this->_quote->getExtensionAttributes();
                if ($cartExtension && $cartExtension->getShippingAssignments()) {
                    $cartExtension->getShippingAssignments()[0]
                        ->getShipping()
                        ->setMethod($methodCode);
                }
                $this->_quote->collectTotals();
                $this->quoteRepository->save($this->_quote);
            }
        }
    }
}
