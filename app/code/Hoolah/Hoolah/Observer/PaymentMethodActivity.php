<?php
    namespace Hoolah\Hoolah\Observer;
    
    use Magento\Sales\Model\Order\Email\Sender\OrderSender;
    use Magento\Framework\Event\ObserverInterface;
    
    use Hoolah\Hoolah\Controller\Main as HoolahMain;
    use Hoolah\Hoolah\Helper\ExtSettings as HoolahExtSettings;
    
    class PaymentMethodActivity implements ObserverInterface
    {
        protected $extSettings;
        
        public function __construct(
            HoolahExtSettings $extSettings
        ){
            $this->extSettings = $extSettings;
        }
        
        public function execute(\Magento\Framework\Event\Observer $observer)
        {
            try
            {
                $payment = $observer->getEvent()->getMethodInstance();
                if ($payment->getCode() == \Hoolah\Hoolah\Model\Checkout\ConfigProvider::CODE)
                {
                    $is_available = true;
                    
                    $quote = $observer->getEvent()->getQuote();
                    $result = $observer->getEvent()->getResult();
                    
                    $billingAddress = $quote->getBillingAddress();
                    $billing = $billingAddress->getData();
                    
                    if (!HoolahMain::check_country($this->extSettings->gatewayEnabledCountries(HoolahMain::get_countries()), $billing['country_id']))
                        $is_available = false;
                    
                    $total = floatval($quote->getGrandTotal());
                    
                    //hard-coded max
                    if (($billing['country_id'] == 'SG' && $total > 3000) ||
                        ($billing['country_id'] == 'MY' && $total > 9000))
                        $is_available = false;
                    
                    if ($this->extSettings->gatewayEnabledMinAmount() && $this->extSettings->gatewayEnabledMinAmount() > $total)
                        $is_available = false;
                    
                    if ($this->extSettings->gatewayEnabledMaxAmount() && $this->extSettings->gatewayEnabledMaxAmount() < $total)
                        $is_available = false;
                    
                    if (!$is_available)
                        $result->setData('is_available', false);
                }
            }
            catch (\Throwable $e)
            {
                
            }
        }
    }