<?php
    namespace Hoolah\Hoolah\Gateway\Command;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\API as HoolahAPI;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    
    use Magento\Payment\Gateway\Command\CommandException;

    use Magento\Payment\Gateway\CommandInterface;
    use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
    use Magento\Payment\Gateway\Http\ClientException;
    use Magento\Payment\Gateway\Http\ClientInterface;
    use Magento\Payment\Gateway\Http\ConverterException;
    use Magento\Payment\Gateway\Http\TransferFactoryInterface;
    use Magento\Payment\Gateway\Request\BuilderInterface;
    use Magento\Payment\Gateway\Response\HandlerInterface;
    use Magento\Payment\Gateway\Validator\ResultInterface;
    use Magento\Payment\Gateway\Validator\ValidatorInterface;
    
    class Refund implements CommandInterface
    {
        protected $hlog;
        protected $hdata;
        
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            \Hoolah\Hoolah\Helper\Log $hlog,
            HoolahData $hdata
        )
        {
            $this->hlog = $hlog;
            $this->hdata = $hdata;
        }
     
        public function execute(array $commandSubject)
        {
            $payment = $commandSubject['payment']->getPayment();
            $order = $payment->getOrder();//$buildSubject['payment']->getOrder();
            $store = $order->getStore();
            $amount = floatval($commandSubject['amount']);
            $total = floatval($order->getGrandTotal());
            $reason = @$_REQUEST['creditmemo']['comment_text'];
            
            $this->hlog->notice('refund: '.$order->getEntityId().':'.$order->getHoolahOrderRef().' started (amount: '.$amount.', reason: '.$reason.')');
            
            //if (!$this->can_refund_order($order)) ?!
            //{
            //    $this->hlog->notice('hoolah refund: can\'t refund the order');
            //    throw new CommandException(__('Refund failed.', 'woocommerce'));
            //}
            
            if ($this->hdata->credentials_are_empty($store))
            {
                $this->hlog->notice('refund: merchant credentials are empty');
                throw new CommandException(__('Merchant credentials are empty'));
            }
            
            $api = new HoolahAPI(
                $this->hdata->get_merchant_id($store),
                $this->hdata->get_merchant_secret($store),
                $this->hdata->get_hoolah_url($store)
            );
            
            $response = $api->merchant_auth_login();
            if (!HoolahAPI::is_200($response))
            {
                $this->hlog->notice('refund: merchant auth error ('.HoolahAPI::get_message($response).')');
                throw new CommandException(__('Merchant auth error ('.HoolahAPI::get_message($response).')'));
            }
            
            if ($amount == $total) // full refund
            {
                $this->hlog->notice('refund: full refund');
                $response = $api->merchant_order_full_refund($order->getHoolahOrderRef(), $reason);
                if (!HoolahAPI::is_202($response))
                {
                    $this->hlog->notice('refund: order full refund error ('.HoolahAPI::get_message($response).')');
                    throw new CommandException(__('Order full refund error ('.HoolahAPI::get_message($response).')'));
                }
            }
            else // partial refund
            {
                $this->hlog->notice('refund: partial refund');
                
                $items = array();
                
                foreach ($order->getAllItems() as $item)
                {
                    $qty = @$_REQUEST['creditmemo']['items'][$item->getId()];
                    if ($qty && isset($qty['qty']))
                        $qty = $qty['qty'];
                    if ($qty)
                    {
                        $product = $item->getProduct();
                        
                        if ($product)
                            $items[] = array('sku' => $product->getSku());
                    }
                }
                $items = array_filter($items);
                
                $response = $api->merchant_order_partial_refund($order->getHoolahOrderRef(), $amount, $reason, $items);
                if (!HoolahAPI::is_202($response))
                {
                    $this->hlog->notice('refund: order partial refund error ('.HoolahAPI::get_message($response).')');
                    throw new CommandException(__('Order partial refund error ('.HoolahAPI::get_message($response).')'));
                }
            }
            
            $this->hlog->notice('refund: success');
        }
    }