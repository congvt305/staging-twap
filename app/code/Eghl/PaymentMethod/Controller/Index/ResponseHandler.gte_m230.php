<?php
namespace Eghl\PaymentMethod\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Eghl\PaymentMethod\Classes\Logger;
use Magento\Framework\Controller\ResultFactory;

class ResponseHandler extends Action implements CsrfAwareActionInterface{

	protected $helperData;
	protected $request;
	protected $urlType;
	protected $_debug;
	protected $_order;
	protected $_OrderCommentSender;
	protected $_invoiceService;
	protected $_transaction;
	protected $_transactionBuilder;
	protected $invoiceSender;
	protected $formKey;
	protected $cart;
    protected $currency;
	protected $Forex_rates;
	protected $stockRegistry;
	protected $checkoutSession;
	protected $messageManager;
	protected $_resultFactory;

	const FirstCallabackFootstep = 'eGHL Callback Recieved';

	/**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
	}

	public function __construct(
		Context $context
	)
	{
		parent::__construct($context);
		$this->_objectManager = ObjectManager::getInstance();
		$this->_resultFactory = $context->getResultFactory();
	}

	protected function initApp(){
		$this->helperData = $this->_objectManager->create('\Eghl\PaymentMethod\Helper\Data');
		$this->_debug = $this->helperData->getGeneralConfig('debug');
		$this->request = $this->_objectManager->create('\Magento\Framework\App\Request\Http');
		$this->urlType = $this->request->getParam('urlType');
		$this->_OrderCommentSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');
		$this->checkoutSession = $this->_objectManager->create('\Magento\Checkout\Model\Session');
		$this->_invoiceService = $this->_objectManager->create('\Magento\Sales\Model\Service\InvoiceService');
		$this->_transaction = $this->_objectManager->create('\Magento\Framework\DB\Transaction');
		$this->_transactionBuilder = $this->_objectManager->create('\Magento\Sales\Model\Order\Payment\Transaction\Builder');
		$this->invoiceSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
		$this->formKey = $this->_objectManager->create('\Magento\Framework\Data\Form\FormKey');
		$this->cart = $this->_objectManager->create('\Magento\Checkout\Model\Cart');
        $this->currency = $this->_objectManager->create('\Magento\Directory\Model\Currency');
		$this->stockRegistry = $this->_objectManager->create('\Magento\CatalogInventory\Api\StockRegistryInterface');
		$this->messageManager = $this->_objectManager->create('\Magento\Framework\Message\ManagerInterface');

		try{
			// Setting area code to remove area code exceptions
			$this->_objectManager->get('Magento\Framework\App\State')->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
		}
		catch(\Exception $e){
			$this->helperData->add_log("EghlApp (Exception) -> ".$e->getMessage());
		}
	}

	protected function add_log($message){
		if($this->_debug){
			$this->helperData->add_log("EghlApp -> ".$this->urlType." -> ".$message);
		}
	}

	protected function refillCart(){
		Logger::writeString('['.$this->urlType.'] Refilling Cart');
		$items = $this->_order->getAllItems();
		$form_key = $this->formKey->getFormKey();
		foreach($items as $item) {
			$productId = $item->getProductId();
			$params = array(
                'form_key' => $form_key,
                'product' => $productId,
                'qty'   => $item->getQtyOrdered()
            );
			//Load the product based on productID
			$product = $this->_objectManager->create('\Magento\Catalog\Model\Product');
			$_product = $product->load($productId);

			// For adding product attributes configuration of any
			if ($item->getProductType() == 'configurable'){
				$productOptionsArray = $item->getProductOptions()['info_buyRequest'];
            	$configurableSuperAtr = $productOptionsArray['super_attribute'];
				$params['super_attribute'] = $configurableSuperAtr;
			}

			$this->cart->addProduct($_product, $params);
		}
		$this->cart->save();
	}

	protected function createOrderInvoice(){
        $canInvoice = $this->_order->canInvoice();
        Logger::writeString('['.$this->urlType.'] has Invoice check '.json_encode($this->_order->hasInvoices(),1));
        if($canInvoice && !$this->_order->hasInvoices()) {
            $vars = $this->request->getParams();
            $this->_order->addStatusHistoryComment('Create Invoice.')->setIsCustomerNotified(false)->save();
            /**
             * @var \Magento\Sales\Model\Order\Invoice $invoice
             */
            $invoice = $this->_invoiceService->prepareInvoice($this->_order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $invoice->setTransactionId( $vars['TxnID'] );
            $invoice->save();
            $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            //send notification code
            $this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getIncrementId()))->setIsCustomerNotified(true)->save();
            Logger::writeString('['.$this->urlType.'] Invoice Sent');
        }
    }

    protected function createTransaction($order = null, $paymentData = array())
    {
        //get payment object from order object
        $payment = $order->getPayment();
        $payment->setLastTransId($paymentData['TxnID']);
        $payment->setTransactionId($paymentData['TxnID']);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
        );
        $formatedPrice = $order->getOrderCurrencyCode().' '.number_format($order->getGrandTotal(), 2, '.', '');

        $message = __('The authorized amount is %1.', $formatedPrice);
        //get the object of builder class
        $trans = $this->_transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData['TxnID'])
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
            )
            ->setFailSafe(true)
            //build method creates the transaction and returns the object
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );
        $payment->setParentTransactionId(null);
        $payment->save();
        $order->save();
        Logger::writeString('['.$this->urlType.'] Magento Transaction created for this order. Authorise message ['.$message.']');
        return  $transaction->save()->getTransactionId();
    }

    protected function orderStatusUpdate($status=NULL){
		Logger::writeString("[orderStatusUpdate: ".$status."] start");
		if(!Logger::hasString(self::FirstCallabackFootstep) || $this->urlType!="return"){
			// will update the status only if order status is 'pending_payment' and $status is not null
			$prevStatus = $this->_order->getStatus();
			if(!is_null($status) && "pending_payment"==$prevStatus){
				$vars = $this->request->getParams();
				$this->_order->setStatus($status);
				$this->_order->setState($status);
				$bSentEmail = true;
				$comment = "[Payment Processed by eGHL] ". $vars['CurrencyCode'] . $vars['Amount'];
				if($vars['TxnStatus']=='1'){
					if($vars['TxnMessage']!="Buyer cancelled"){
						$comment .= " [Transaction ID:" . $vars['TxnID'] . "]" . " [Payment method: " . $vars['PymtMethod'] . "]"." [Order ID:" . $vars['OrderNumber'] . "]";
					}
					//$comment .= " [TxnMessage:".$vars['TxnMessage']."]";
					if(!$this->helperData->getGeneralConfig('fail_payment_email')){
						$bSentEmail = false;
					}
				}
				elseif($status == 'fraud'){
					$comment .= ' Order amount ['.$this->_order->getOrderCurrencyCode().' '.$this->_order->getGrandTotal().'] not tally with eGHL amount ['.$vars['CurrencyCode'].' '.$vars['Amount'].']';
				}
				else{
					$comment .= " [Transaction ID:" . $vars['TxnID'] . "]" . " [Payment method: " . $vars['PymtMethod'] . "]"." [Order ID:" . $vars['OrderNumber'] . "]";
				}

				$history = $this->_order->addStatusHistoryComment($comment, false);
				if($bSentEmail){
					$history->setIsCustomerNotified(true);
				}
				$this->_order->save();
				Logger::writeString('['.$this->urlType.'] Order Status Changed from ['.$prevStatus.'] to ['.$status.']');
				Logger::writeString('['.$this->urlType.'] Order Comment added as >> '.$comment);
				if($bSentEmail){
					//$this->_OrderCommentSender->send($this->_order, true, $comment);
				}
				$this->_order->addStatusHistoryComment("Monitoring -> OrderStatus:$status, payment_success_status setting:".$this->helperData->getGeneralConfig('payment_success_status'))->setIsCustomerNotified(false)->save();
				if($status==$this->helperData->getGeneralConfig('payment_success_status')){
					$this->createOrderInvoice();
					$this->createTransaction($this->_order,$vars);
				}
			}
		}
        else{
            Logger::writeString("Callback identifier string is found so doing nothing.");
        }
		Logger::writeString("[orderStatusUpdate: ".$status."] end");
    }

	protected function setOrdertoFraud(){
		$this->orderStatusUpdate('fraud');
		if(strtolower($this->urlType)=='callback'){
			die('ERROR');
		}
		elseif(strtolower($this->urlType)=='return'){
			$redirLoc = $this->helperData->getBaseURL().'checkout/onepage/failure';
            switch($this->helperData->getGeneralConfig('mid')){
                case 'DIY':
                case 'DY2':
                case 'DIQ':
                    $redirLoc = $this->helperData->getBaseURL().'payment-failed'; // Mr.DIY requirement
                break;
                default:
                    $redirLoc = $this->helperData->getBaseURL().'checkout/onepage/failure';
                break;
            }
            Logger::writeString('['.$this->urlType.'] Redirecting to >> '.$redirLoc);
            $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($redirLoc);
			return $resultRedirect;
		}
	}

	protected function calculate_hash2($vars){
		$clear_string = $this->helperData->getGeneralConfig('hashpass');
		// Hash2 String before Hashing: TxnID.ServiceID.PaymentID.TxnStatus.Amount.CurrencyCode.AuthCode.OrderNumber

		$hashStrKeysOrder = array (
			'TxnID',
			'ServiceID',
			'PaymentID',
			'TxnStatus',
			'Amount',
			'CurrencyCode',
			'AuthCode',
			'OrderNumber',
		);

		//Here we construct the hash string according to the payment gateway's requirements
		foreach ($hashStrKeysOrder as $key)
		{
			if(isset($vars[$key])){
				$clear_string .= $vars[$key];
			}
		}

		$this->add_log("clear_string: $clear_string");
		return hash('sha256', $clear_string);
	}

	public function p_arr($arr, $prefix=NULL){
		if(is_null($prefix)){
			echo "<pre>".print_r($arr,1)."</pre>";
		}
		else{
			echo "<pre>$prefix: ".print_r($arr,1)."</pre>";
		}
	}

	public function execute()
	{
		try{

			$this->initApp();

			// get all request params
			$vars = $this->request->getParams();
			$this->add_log('vars: '.print_r($vars,1));

			if(strtolower($this->urlType)=='callback'){
				$this->add_log('vars: '.print_r($vars,1));
			}

			if(isset($vars['OrderNumber'])){

				// instanciate order object
				$this->_order = $this->_objectManager->create('\Magento\Sales\Model\Order');
				// load order by ID
				$this->_order->loadByIncrementId($vars['OrderNumber']);

				if(strtolower($this->urlType)=='ordershipping'){
					$output = array(
										'BaseShipping'=>number_format($this->_order->getBaseShippingAmount(),2,'.',''),
										'DisplayShipping'=>number_format($this->_order->getShippingAmount(),2,'.','')
									);
					echo json_encode($output);
				}
				else{
					Logger::init($vars['OrderNumber']);
					Logger::writeArray($vars,'['.$this->urlType.'_'.$vars['TxnStatus'].'] Response received from eGHL');
					Logger::writeString('['.$this->urlType.'] Direct values from order object >> CurrencyCode['.$this->_order->getOrderCurrencyCode().'] GrandTotal['.$this->_order->getGrandTotal().']');
					// Amount validation if currency is same
					if($vars['CurrencyCode']==$this->_order->getOrderCurrencyCode() && $vars['Amount']!=$this->_order->getGrandTotal()){
						$this->setOrdertoFraud();
					}
					else{
						$conversionError = 0.1; // ideally the conversion error must be 0 but set it to some value less than 1 as mathematical precision may cause slight difference
						//Amount validation if currency is different
						if($vars['CurrencyCode']!=$this->_order->getOrderCurrencyCode()){
							$this->Forex_rates = $this->helperData->getForexData($this->_order->getOrderCurrencyCode());
							Logger::writeArray($this->Forex_rates,'['.$this->urlType.'] Forex Rates calculated with base currency set as:'.$this->_order->getOrderCurrencyCode());

							$ConvertedAmt = 0;
							foreach($this->Forex_rates as $rate){
								if($rate['curr'] == $vars['CurrencyCode']){
									$ConvertedAmt = number_format(($vars['Amount']/floatval($rate['rate'])), 2, '.','');
									break;
								}
							}
							Logger::writeString('['.$this->urlType.'] '.$vars['Amount'].' '.$vars['CurrencyCode'].' Converted to: '.$ConvertedAmt.' '.$this->_order->getOrderCurrencyCode());
							$AbsDiff = abs(floatval($ConvertedAmt)-floatval($this->_order->getGrandTotal()));
							Logger::writeString('['.$this->urlType.'] Absolute Difference: '.$AbsDiff);
							if( $AbsDiff > $conversionError ){
								$this->setOrdertoFraud();
							}
						}

						// Proceed only if TransactionType = SALE
						if($vars['TransactionType']=="SALE"){
							$hash2 = $this->calculate_hash2($vars);
							if (strcasecmp($hash2,$vars['HashValue2'])!=0) //Different hash between what we calculate and the hash sent by the payment platform so we do not do anything as we consider that the notification doesn't come from the payment platform.
							{
								Logger::writeString('['.$this->urlType.'] Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.'))');
								$this->add_log('Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')');
								$this->p_arr('Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')');
								echo 'Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')';
							}
							else{

								if(Logger::hasString(self::FirstCallabackFootstep) && $this->urlType=="callback"){
									Logger::writeString("Callback identifier string is found so exiting");
									die("OK");
								}

								// Logic for ignoring duplicate callback
								// The logic is to read the log file and find for the string [callback_0] or [callback_1]
								// this means the callback is already recieved so we apply footstep taht will cause duplicate callback to exit the code
								$hasString = Logger::hasString('[callback_0]') || Logger::hasString('[callback_1]');
								if($hasString && $this->urlType=="callback"){
									// Apply footstep
									Logger::writeString(self::FirstCallabackFootstep);
								}

								if($vars['TxnStatus']=='0') // Success
								{
                                    $this->messageManager->addSuccessMessage("Payment Successful! Message from eGHL [".$vars['TxnMessage']."]");
									$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_success_status'));
									$this->add_log('Order Placed');
									if(strtolower($this->urlType)=='return'){

										// Set session
										$this->checkoutSession
											->setLastQuoteId($this->_order->getQuoteId())
											->setLastSuccessQuoteId($this->_order->getQuoteId())
											->clearHelperData();
										if ($this->_order) {
											$this->checkoutSession->setLastOrderId($this->_order->getId())
												->setLastRealOrderId($this->_order->getIncrementId());
										}

										$redirLoc = $this->helperData->getBaseURL().'checkout/onepage/success';
										Logger::writeString('['.$this->urlType.'] Redirecting to >> '.$redirLoc);
										header("Location: ".$redirLoc);
										die();
									}
								}
								elseif($vars['TxnStatus']=='1') //Return code different from success so we set the "invalid" status to the order
								{
									$this->messageManager->addErrorMessage("Payment Failed! Message from eGHL [".$vars['TxnMessage']."]");
									if($vars['TxnMessage']=='Buyer cancelled') //Buyer clicked cancel payement so donot treat it as failed transaction
									{
										$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_cancel_status'));
										$this->add_log('Order Canceled');
										if(strtolower($this->urlType)=='return'){
											// Restock when order canceled from payment page
											$this->restockOrder();
										}
									}
									else{
										$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_fail_status'));
										$this->add_log('Payment Failed');
									}
									if(strtolower($this->urlType)=='return'){
										$redirLoc = $this->helperData->getBaseURL().'checkout/onepage/failure';
										switch($this->helperData->getGeneralConfig('mid')){
											case 'DIY':
											case 'DY2':
											case 'DIQ':
												$redirLoc = $this->helperData->getBaseURL().'payment-failed'; // Mr.DIY requirement
											break;
											default:
												$redirLoc = $this->helperData->getBaseURL().'checkout/onepage/failure';
											break;
										}
										$this->refillCart();
										Logger::writeString('['.$this->urlType.'] Redirecting to >> '.$redirLoc);
										header("Location: ".$redirLoc);
-                                       die();
									}
									elseif(strtolower($this->urlType)=='callback'){
										// only Restock in callback
										$this->restockOrder();
									}
								}
								elseif($vars['TxnStatus']=='2') // Pending response
								{
									$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_pending_status'));
									$this->add_log('Payment Pending');
									if(strtolower($this->urlType)=='return'){
										$redirLoc = $this->helperData->getBaseURL().'eghlgwopt?OrderNumber='.$vars['OrderNumber'].'&gwresp=pending';
										Logger::writeString('['.$this->urlType.'] Redirecting to >> '.$redirLoc);
										header("Location: ".$redirLoc);
-                                       die();
									}
								}

								if(strtolower($this->urlType)=='callback'){
									Logger::writeString('['.$this->urlType.'] OK acknowledgement sent back to eGHL');
									print "OK"; // acknowledgement sent to payment gateway
									$this->add_log('acknowledgement sent to payment gateway');
								}
							}
						}
						else{
							$this->add_log('Invalid TransactionType i.e. '.$vars['TransactionType']);
						}
					}

				}

			}
			else{
				$this->p_arr('"OrderNumber" is missing');
				$this->add_log('"OrderNumber" is missing');
			}

		}
		catch (\Exception $e)
		{
			$this->add_log('Exception: '.print_r($e->getMessage(),1));
			echo "Exception: ".$e->getMessage();
		}
  }

  	private function restockOrder(){
		Logger::writeString('['.$this->urlType.'] Restocking');
		$items = $this->_order->getAllItems();
		foreach($items as $item) {
			Logger::writeString(
				'getQtyToCancel: ' . $item->getQtyToCancel()
			);

			$item->cancel();
			$item->save();
		}
	}

	// Obsoleted
	private function RestockProduct($sku, $Additonal)
	{
		$stockItem = $this->stockRegistry->getStockItemBySku($sku);
		$CurrentQty = $stockItem->getQty();
		Logger::writeString('Current Quantity = '.$CurrentQty);
		$newQty = $CurrentQty + $Additonal;
		$difference = $newQty - $CurrentQty;
		Logger::writeString('Quantity Diff = '.$difference);
		$stockItem->setQtyCorrection($difference);
		$this->stockRegistry->updateStockItemBySku($sku, $stockItem);

		$stockItem = $this->stockRegistry->getStockItemBySku($sku);
		Logger::writeString('After Restock = '.$stockItem->getQty());
		if($newQty > 0){
			Logger::writeString('Setting stock status to InStock');
			$stockItem->setIsInStock(true);
			$this->stockRegistry->updateStockItemBySku($sku, $stockItem);
		}
	}

}
?>
