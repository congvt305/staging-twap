<?php

namespace Atome\MagentoPayment\Controller\Payment;


use Atome\MagentoPayment\Services\Config\PaymentGatewayConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\OrderRepository;

class Test extends AtomeAction
{

    /**
     * This method is used by Atome to verify that the server callbacks are normal
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {

        $orderId = 72;

        $objectManager = ObjectManager::getInstance();
        $order = $objectManager->get(OrderRepository::class)->get($orderId);

        dd($order->getStatus(),$order->getState());
    }
}
