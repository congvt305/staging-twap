<?php
    namespace Hoolah\Hoolah\Observer;
    
    use Magento\Sales\Model\Order\Email\Sender\OrderSender;
    use Magento\Framework\Event\ObserverInterface;
    
    class BeforeQuoteSubmit implements ObserverInterface
    {
        public function execute(\Magento\Framework\Event\Observer $observer)
        {
            try
            {
                $order = $observer->getEvent()->getOrder();
                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                
                if ($method->getCode() == \Hoolah\Hoolah\Model\Checkout\ConfigProvider::CODE)
                    $order->setCanSendNewEmailFlag(false);
            }
            catch (\Throwable $e)
            {
                
            }
        }
    }