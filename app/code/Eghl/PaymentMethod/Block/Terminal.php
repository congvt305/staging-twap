<?php

namespace Eghl\PaymentMethod\Block;

use Eghl\PaymentMethod\Classes\Logger;
use Magento\Sales\Model\OrderFactory;

class Terminal extends \Magento\Framework\View\Element\Template
{
	protected $helperData;
	protected $request;
	protected $_store;
	protected $debug_html;
	protected $_objectManager;
	protected $_order;
	protected $_checkoutSession;

	public function __construct(
								\Magento\Framework\View\Element\Template\Context $context,
								\Eghl\PaymentMethod\Helper\Data $helperData,
								\Magento\Framework\App\Request\Http $request,
								\Magento\Store\Api\Data\StoreInterface $store,
								\Magento\Sales\Model\OrderFactory $orderFactory,
								\Magento\Checkout\Model\SessionFactory $checkoutSession
								)
	{
		$this->helperData = $helperData;
		$this->request = $request;
		$this->_store = $store;
		$this->_order = $orderFactory;
		$this->_checkoutSession = $checkoutSession;

		$this->debug_html = "";
		parent::__construct($context);
	}

	public function _prepareLayout()
	{
		//set page title
		$gwresp = $this->getParam('gwresp');
		if(!is_null($gwresp) && $gwresp!=""){
			if("success"==$gwresp){
				$this->pageConfig->getTitle()->set(__("Payment Successful!"));
			}
			elseif("failed"==$gwresp){
				$this->pageConfig->getTitle()->set(__("Payment Failed!"));
			}
			elseif("pending"==$gwresp){
				$this->pageConfig->getTitle()->set(__("Payment Pending!"));
			}
			elseif("canceled"==$gwresp){
				$this->pageConfig->getTitle()->set(__("Payment Canceled!"));
			}
		}
		else{
			$this->pageConfig->getTitle()->set(__('Redirecting to eGHL Payment Gateway'));
		}

		return parent::_prepareLayout();
	}

	public function getEghlConfig($config){
		return $this->helperData->getGeneralConfig($config);
	}

	public function getPost($param, $default=NULL){
		return $this->request->getPost($param, $default);
	}

	public function getParam($param, $default=NULL){
		return $this->request->getParam($param, $default);
	}

	public function getParams($params, $default=NULL){
		return $this->request->getParams($params, $default);
	}

	public function eghl_acceptable_locale(){
		list($first,$second) =  explode('_',$this->helperData->getCurrentLocale());
		return $first;
	}

	protected function calculateHashValue(&$pgw_params){
		$clearString	=	$this->getEghlConfig('hashpass');
		$hashStrKeysOrder = array (
			'ServiceID',
			'PaymentID',
			'MerchantReturnURL',
			'MerchantCallBackURL',
			'Amount',
			'CurrencyCode',
			'CustIP',
			'PageTimeout',
		);
		foreach($hashStrKeysOrder as $ind){
			$clearString	.=	$pgw_params[$ind];
		}
		$pgw_params["HashValue"]	=	hash('sha256', $clearString);
	}

	public function content_controller(){

		$gwresp = $this->getParam('gwresp');
		$order_id = $this->getParam('OrderNumber');
		$content = "";
		$reorder = "";
		if(!is_null($gwresp) && $gwresp!=""){
			if("success"==$gwresp){
				$content .= "<p>".__("Thanks for purchasing with us.")."</p>";
			}
			elseif("failed"==$gwresp){
				$content .= "<p>".__("Something went wrong. Your order is under review with us.")."</p>";
				$reorder = "<a href='".$this->helperData->getBaseURL()."eghlgw/Index/Copyquote?order_id=".$order_id."' class='button action continue primary'>".__("Reorder")."</a>";
			}
			elseif("pending"==$gwresp){
				$content .= "<p>".__("Your order status is pending with us.")."</p>";
			}
			elseif("canceled"==$gwresp){
				$content .= "<p>".__("The order was canceled by you.")."</p>";
			}
			$content .= "	<center>
								".$reorder."
								<a class='eGHL_btn button action continue' href='".$this->helperData->getBaseURL()."sales/order/view/order_id/$order_id'>View Order</a>
							</center>";
		}
		else{
			$content .= $this->eGHLPaymentForm();
		}

		return $content;
	}

	public function eGHLPaymentForm(){
		// load order by ID
		if(is_numeric($this->getPost('OrderNumber'))){
			// in case of logged in user
            $order = $this->_order->create()->loadByAttribute('quote_id',$this->getPost('OrderNumber'));
			Logger::init($order->getIncrementId());
            Logger::writeString('Order created by logged in Customer ['.$order->getCustomerName().'] -> Magento quote_id ['.$order->getQuoteId().']');
		}
		else{
			// in case of guest checkout
			$order = $this->_checkoutSession->create()->getLastRealOrder();
			Logger::init($order->getIncrementId());
            Logger::writeString('Order created by logged in Customer ['.$order->getCustomerName().'] -> Magento quote_id ['.$this->getPost('OrderNumber').']');
		}

		$CurrencyCode = '';
		$amount = 0;
		$shipping = 0;

		Logger::writeString('Direct values from order object >> CurrencyCode['.$order->getOrderCurrencyCode().'] GrandTotal['.$order->getGrandTotal().']');
		$CurrencyCode = $order->getOrderCurrencyCode();
		$amount = number_format($order->getGrandTotal(), 2, '.','');

		$pgw_params	=	array(
							"TransactionType"	=>	"SALE",
							"PymtMethod"	=>	$this->getEghlConfig('pay_method'),
							"ServiceID"	=>	$this->getEghlConfig('mid'),
							"PaymentID"	=>	$this->helperData->genPaymentID(),
							"OrderNumber"	=>	$order->getIncrementId(),
							"PaymentDesc"	=>	$this->getPost('PaymentDesc'),
							"Amount"	=>	number_format(($amount+$shipping), 2, '.',''),
							"CurrencyCode"	=>	$CurrencyCode,
							"CustIP"	=>	$this->getPost('CustIP'),
							"CustName"	=>	$this->getPost('CustName'),
							"CustEmail"	=>	$this->getPost('CustEmail'),
							"CustPhone"	=>	$this->getPost('CustPhone'),
							"LanguageCode"	=> $this->eghl_acceptable_locale(),
							"PageTimeout"	=>	$this->getEghlConfig('page_timeout'),
							"MerchantReturnURL"	=>	$this->helperData->getBaseURL()."eghlapi/Index/ResponseHandler/?urlType=return",
							"MerchantCallBackURL"	=>	$this->helperData->getBaseURL()."eghlapi/Index/ResponseHandler/?urlType=callback"
						);
		$this->calculateHashValue($pgw_params);
		Logger::writeArray($pgw_params,'Payment request parameters being sent to eGHL at URL ['.$this->getEghlConfig('payment_url').']');
		$this->redirect($this->getEghlConfig('payment_url'),$pgw_params);
		exit;

	}

	private function redirect($URL, $data){
		$URL;
		$data = http_build_query($data);

		header("Location: $URL?$data");
	}

	protected function add_log($message){
		if($this->getEghlConfig('debug')){
			$this->helperData->add_log(print_r($message,1));
		}
	}
}
?>
