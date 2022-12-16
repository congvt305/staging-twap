<?php

namespace CJ\CustomAtome\Controller\Overridden\Payment;

/**
 * Class Prepare
 */
class Prepare extends \Atome\MagentoPayment\Controller\Payment\Prepare
{
    /**
     * @var \Magento\SalesRule\Model\Coupon\UpdateCouponUsages
     */
    protected $updateCouponUsages;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Atome\MagentoPayment\Services\Config\PaymentGatewayConfig $paymentGatewayConfig
     * @param \Atome\MagentoPayment\Services\Price\PriceService $priceService
     * @param \Magento\SalesRule\Model\Coupon\UpdateCouponUsages $updateCouponUsages
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Atome\MagentoPayment\Services\Config\PaymentGatewayConfig $paymentGatewayConfig,
        \Atome\MagentoPayment\Services\Price\PriceService $priceService,
        \Magento\SalesRule\Model\Coupon\UpdateCouponUsages $updateCouponUsages,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\OrderService $orderService
    ) {
        $this->updateCouponUsages = $updateCouponUsages;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;

        parent::__construct($context, $checkoutSession, $paymentGatewayConfig, $priceService);
    }

    /**
     * {@inheritDoc}
     */
    protected function cancelOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $this->orderService->cancel($orderId);
        $this->updateCouponUsages->execute($order, false);
    }
}
