<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/22/20
 * Time: 7:00 AM
 */

namespace Eguana\GWLogistics\Controller\Test;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Shippingmethod extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {

        /** @var \Magento\Quote\Api\Data\CartInterface $order */
//        $quote = $this->quoteRepository->get(40);
        $order = $this->orderRepository->get(37);
        $shippingMethod = $order->getShippingMethod(true);
        var_dump($shippingMethod);

    }
}
