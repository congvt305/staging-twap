<?php

namespace Ecpay\Ecpaypayment\Helper;

use Exception;
use Ecpay\Ecpaypayment\Model\Order as EcpayOrderModel;
use Ecpay\Ecpaypayment\Model\Payment as EcpayPaymentModel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;

include_once('Library/ECPayPaymentHelper.php');

class Data extends AbstractHelper
{
    /**
     * @var EcpayOrderModel
     */
    protected $_ecpayOrderModel;

    /**
     * @var EcpayPaymentModel
     */
    protected $_ecpayPaymentModel;

    /**
     * @var ModuleListInterface
     */
    private $_moduleList;

    /**
     * @var ProductMetadataInterface
     */
    private $_productMetadata;

    /**
     * @var string
     */
    private $prefix = 'ecpay_';

    /**
     * @var array
     */
    private $errorMessages = array();
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    private $transactionBuilder;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        EcpayOrderModel $ecpayOrderModel,
        EcpayPaymentModel $ecpayPaymentModel,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_ecpayOrderModel = $ecpayOrderModel;
        $this->_ecpayPaymentModel = $ecpayPaymentModel;
        $this->_moduleList = $moduleList;
        $this->_productMetadata = $productMetadata;
        $this->errorMessages = array(
            'invalidPayment' => __('Invalid payment method'),
            'invalidOrder' => __('Invalid order'),
        );
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->transactionBuilder = $transactionBuilder;
        $this->orderRepository = $orderRepository;
    }

    public function getChoosenPayment()
    {
        $session = $this->_ecpayOrderModel->getAdditionalInformation();

        if (empty($session['ecpay_choosen_payment']) === true) {
            return '';
        } else {
            return $session['ecpay_choosen_payment'];
        }
    }

    public function getEcpayConfig($id)
    {
        return $this->_ecpayPaymentModel->getEcpayConfig($id);
    }

    public function getMagentoConfig($id)
    {
        return $this->_ecpayPaymentModel->getMagentoConfig($id);
    }

    public function getErrorMessage($name, $value)
    {
        $message = $this->errorMessages[$name];
        if ($value !== '') {
            return sprintf($message, $value);
        } else {
            return $message;
        }
    }

    public function getPaymentTranslation($payment)
    {
        $text = 'ecpay_payment_text_' . strtolower($payment);
        return __($text);
    }

    public function getRedirectHtml()
    {
        try {

            $sdkHelper = $this->_ecpayPaymentModel->getHelper();

            // Validate the order id
            $orderId = $this->_ecpayOrderModel->getOrderId();
            if (!$orderId) {
                return $this->setFailureStauts($this->getErrorMessage('invalidOrder', ''));
            }

            // Get the order
            $order = $this->_ecpayOrderModel->getOrder($orderId);
            if (!$order) {
                return $this->setFailureStauts($this->getErrorMessage('invalidOrder', ''));
            }

            // Validate choose payment
            $choosenPayment = $this->getChoosenPayment();
            $paymentName = $this->getPaymentTranslation($choosenPayment);
            if ($this->_ecpayPaymentModel->isValidPayment($choosenPayment) === false) {
                return $this->setFailureStauts($this->getErrorMessage('invalidPayment', $paymentName), $order);
            }

            // Validate currency code
            $baseCurrencyCode = $order->getBaseCurrencyCode();
            $orderCurrencyCode = $order->getOrderCurrencyCode();
            if ($baseCurrencyCode !== 'TWD' || $orderCurrencyCode !== 'TWD') {
                return $this->setFailureStauts($order, $this->getErrorMessage('invalidOrder', ''));
            }

            // Update order status and comments
            $createStatus = $this->getMagentoConfig('order_status');
            $comment = __('Payment Method: %1', $paymentName);

            $this->setOrderCommentForFront($order, $comment, $createStatus, false);

            // Checkout
            $helperData = array(
                'choosePayment' => $choosenPayment,
                'hashKey' => $this->getEcpayConfig('hash_key'),
                'hashIv' => $this->getEcpayConfig('hash_iv'),
                'returnUrl' => $this->_ecpayPaymentModel->getModuleUrl('response'),
                'clientBackUrl' => $this->_ecpayPaymentModel->getMagentoUrl('checkout/onepage/success'),
                'orderId' => $orderId,
                'total' => $order->getGrandTotal(),
                'itemName' => __('A Package Of Online Goods'),
                'cartName' => 'magento_' . $this->getModuleVersion(),
                'currency' => $orderCurrencyCode,
                'needExtraPaidInfo' => 'Y',
            );

            $sdkHelper->checkout($helperData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getPaymentResult($paymentData)
    {
        $resultMessage = '1|OK';
        $error = '';
        $orderId = null;

        try {
            $sdkHelper = $this->_ecpayPaymentModel->getHelper();

            // Get valid feedback
            $helperData = array(
                'hashKey' => $this->getEcpayConfig('hash_key'),
                'hashIv'  => $this->getEcpayConfig('hash_iv'),
            );
            $feedback = $sdkHelper->getValidFeedback($helperData);
            unset($helperData);

            if (count($feedback) < 1) {
                throw new Exception('Get ECPay feedback failed.');
            } else {
                $orderId = $sdkHelper->getOrderId($feedback['MerchantTradeNo']);
                $order = $this->_ecpayOrderModel->getOrder($orderId);

                // Check transaction amount and currency
                if (!($order->getOrderCurrencyCode())) {
                    $orderTotal = $order->getGrandTotal();
                    $currency = $order->getOrderCurrencyCode();
                } else {
                    $orderTotal = $order->getBaseGrandTotal();
                    $currency = $order->getBaseCurrencyCode();
                }

                // Check the amounts
                if ($sdkHelper->validAmount($feedback['TradeAmt'], $orderTotal) === false) {
                    throw new Exception(sprintf('Order %s amount are not identical.', $orderId));
                }

                // Get the response status
                $orderStatus = $order->getStatus();
                $createStatus =  $this->getMagentoConfig('order_status');

                $helperData = array(
                    'validState' => ($orderStatus === $createStatus),
                    'orderId' => $orderId,
                );
                $responseStatus = $sdkHelper->getResponseState($feedback, $helperData);
                unset($helperData);

                // Update the order status
                $patterns = array(
                    1 => __('ecpay_payment_order_comment_payment_result'),
                    2 => __('ecpay_payment_order_comment_atm'),
                    3 => __('ecpay_payment_order_comment_cvs'),
                    4 => __('ecpay_payment_order_comment_barcode'),
                );

                switch($responseStatus) {
                    case 1: // Paid
                        $status = $this->getEcpayConfig('success_status');
                        $pattern = $patterns[$responseStatus];
                        $comment = sprintf($pattern, $feedback['RtnCode'], $feedback['RtnMsg']);

                        $this->setOrderCommentForFront($order, $comment, $status, EcpayOrderModel::NOTIFY_PAYMENT_RESULT);

                        $transaction = $this->createTransaction($order, $paymentData);

                        $this->createInvoice($order, $transaction);

                        unset($status, $pattern, $comment);
                        break;
                    case 2: // ATM get code
                    case 3: // CVS get code
                    case 4: // Barcode get code
                        $status = $orderStatus;
                        $pattern = $patterns[$responseStatus];
                        $comment = $sdkHelper->getObtainingCodeComment($pattern, $feedback);

                        $this->setOrderCommentForFront($order, $comment, $status, EcpayOrderModel::NOTIFY_GET_CODE_RESULT);

                        unset($status, $pattern, $comment);
                        break;
                    case 6: // Simulate Paid
                        $status = $orderStatus;
                        $comment = __('Simulate paid, update the note only.');

                        $this->setOrderCommentForBack($order, $comment, $status, EcpayOrderModel::NOTIFY_SIMULATE_PAID);

                        unset($status, $pattern, $comment);
                        break;
                    default:
                }
            }
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $error = $e->getMessage();
            $this->_getSession()->addError($error);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = $e->getMessage();
            $this->_getSession()->addError($error);
        }  catch (Exception $e) {
            $error = $e->getMessage();
        }

        if ($error !== '') {

            if (is_null($orderId) === false) {

                $status = $this->getEcpayConfig('failed_status');
                $pattern = __('ecpay_payment_order_comment_payment_failure');
                $comment = $sdkHelper->getFailedComment($pattern, $error);

                $this->setOrderCommentForFront($order, $comment, $status, EcpayOrderModel::NOTIFY_PAYMENT_RESULT);

                unset($status, $pattern, $comment);
            }

            // Set the failure result
            $resultMessage = '0|' . $error;
        }
        echo $resultMessage;
        exit;
    }

    public function isPaymentAvailable()
    {
        return $this->_ecpayPaymentModel->isPaymentAvailable();
    }

    private function setFailureStauts($comment, $order = null)
    {
        if (!is_null($order)) {
            $status = \Magento\Sales\Model\Order::STATE_CANCELED;

            $this->setOrderCommentForFront($order, $comment, $status, EcpayOrderModel::NOTIFY_CREATE_ORDER_RESULT);
        }

        return [
            'status' => 'Failure',
            'msg' => $comment
        ];
    }

    private function setOrderCommentForBack($order, $comment, $status, $notify)
    {
        $order->addStatusToHistory($status, $comment, $notify)
              ->save();
    }

    private function setOrderCommentForFront($order, $comment, $status, $notify)
    {
        $order->setState($this->_ecpayOrderModel->getOrderState($status))
              ->setStatus($status);

        $history = $order->addStatusHistoryComment($comment, false);
        $history->setIsCustomerNotified($notify);
        $history->setIsVisibleOnFront(true);

        $order->save();

        if ($notify === true) {
            $this->_ecpayOrderModel->emailCommentSender($order, $comment);
        }
    }

    public function getModuleVersion()
    {
        $version = $this->_moduleList->getOne('Ecpay_Ecpaypayment');
        if ($version && isset($version['setup_version'])) {
            return $version['setup_version'];
        } else {
            return null;
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $paymentData
     * @return \Magento\Sales\Api\Data\TransactionInterface
     * @throws Exception
     */
    private function createTransaction(\Magento\Sales\Model\Order $order, $paymentData): \Magento\Sales\Api\Data\TransactionInterface
    {
        $payment = $order->getPayment();
        $originAdditionalInfo = $payment->getAdditionalInformation();
        $mergedData = array_merge($originAdditionalInfo, $paymentData);
        $payment->setLastTransId($paymentData["TradeNo"]);
        $payment->setTransactionId($paymentData["TradeNo"]);
        $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$mergedData]);

        // Formatted price
        $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

        // Prepare transaction
        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData['TradeNo'])
            ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$mergedData])
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        // Add transaction to payment
        $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
        $payment->setParentTransactionId(null);

        // Save payment, transaction and order
        $payment->save();
        $order->save();
        $transaction->save();
        return $transaction;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\TransactionInterface $transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoice(\Magento\Sales\Model\Order $order, \Magento\Sales\Api\Data\TransactionInterface $transaction): void
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();

            if ($invoice->canCapture()) {
                $invoice->capture();
            }

            $invoice->setTransactionId($transaction->getTransactionId());
            $invoice->save();

            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );

            $transactionSave->save();
            $order->save();
        }
    }

    public function createEInvoice($orderId)
    {
        try
        {
            $sMsg = '' ;
            // 1.載入SDK程式
            include_once('Library/Ecpay_Invoice.php') ;
            $ecpay_invoice = new \EcpayInvoice() ;

            // 2.寫入基本介接參數
            $this->initEInvoice($ecpay_invoice);

            $order = $this->orderRepository->get($orderId);
            $payment = $order->getPayment();
            $additionalInfo = $payment->getAdditionalInformation();
            $rawDetailsInfo = $additionalInfo["raw_details_info"];
            $donationValue = $rawDetailsInfo["ecpay_einvoice_donation"];
            $donationCode = $this->getEcpayConfig("invoice/ecpay_invoice_love_code");

            // 3.寫入發票相關資訊
            $aItems = array();
            // 商品資訊
            $this->initOrderItems($order, $ecpay_invoice);

            $RelateNumber = $this->initEInvoiceInfo($ecpay_invoice, $order, $donationValue, $donationCode);

            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            foreach($aReturn_Info as $key => $value) {
                $sMsg .=   $key . ' => ' . $value . '<br>' ;
            }
            $payment->setAdditionalData(json_encode($aReturn_Info));
            $payment->save();
        } catch (Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
        }
        echo 'RelateNumber=>' . $RelateNumber.'<br>'.$sMsg ;
    }

    /**
     * @param \EcpayInvoice $ecpay_invoice
     */
    public function initEInvoice(\EcpayInvoice $ecpay_invoice): void
    {
        $ecpay_invoice->Invoice_Method = 'INVOICE';
        $ecpay_invoice->Invoice_Url = 'https://einvoice-stage.ecpay.com.tw/Invoice/Issue';
        $ecpay_invoice->MerchantID = $this->getEcpayConfig("merchant_id");
        $ecpay_invoice->HashKey = $this->getEcpayConfig("invoice/ecpay_invoice_hash_key");
        $ecpay_invoice->HashIV = $this->getEcpayConfig("invoice/ecpay_invoice_hash_iv");
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \EcpayInvoice $ecpay_invoice
     */
    public function initOrderItems(\Magento\Sales\Api\Data\OrderInterface $order, \EcpayInvoice $ecpay_invoice): void
    {
        $orderItems = $order->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => __($orderItem->getData('name')),
                    'ItemCount' => (int)$orderItem->getData('qty_ordered'),
                    'ItemWord' => '批',
                    'ItemPrice' => $orderItem->getData('price'),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $orderItem->getData('price'),
                    'ItemRemark' => $orderItem->getData('sku')
                )
            );
        }
    }

    /**
     * @param \EcpayInvoice $ecpay_invoice
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $donationValue
     * @param $donationCode
     * @return string
     */
    public function initEInvoiceInfo(\EcpayInvoice $ecpay_invoice, \Magento\Sales\Api\Data\OrderInterface $order, string $donationValue, $donationCode): string
    {
        $RelateNumber = 'ECPAY' . date('YmdHis') . rand(1000000000, 2147483647); // 產生測試用自訂訂單編號
        $ecpay_invoice->Send['RelateNumber'] = $RelateNumber;
        $ecpay_invoice->Send['CustomerID'] = $order->getCustomerId();
        $ecpay_invoice->Send['CustomerIdentifier'] = '';
        $ecpay_invoice->Send['CustomerName'] = $order->getCustomerFirstname() . $order->getCustomerLastname();
        $ecpay_invoice->Send['CustomerAddr'] = $order->getBillingAddress()->getCity();
        $ecpay_invoice->Send['CustomerPhone'] = '';
        $ecpay_invoice->Send['CustomerEmail'] = $order->getCustomerEmail();
        $ecpay_invoice->Send['ClearanceMark'] = '';
        $ecpay_invoice->Send['Print'] = '0';
        $ecpay_invoice->Send['Donation'] = ($donationValue == "true") ? 1 : 0;
        $ecpay_invoice->Send['LoveCode'] = ($donationValue == "true") ? $donationCode : '';
        $ecpay_invoice->Send['CarruerType'] = '';
        $ecpay_invoice->Send['CarruerNum'] = '';
        $ecpay_invoice->Send['TaxType'] = 1;
        $ecpay_invoice->Send['SalesAmount'] = $order->getGrandTotal() - $order->getShippingAmount();
        $ecpay_invoice->Send['InvoiceRemark'] = 'v1.0.190822';
        $ecpay_invoice->Send['InvType'] = '07';
        $ecpay_invoice->Send['vat'] = '';
        return $RelateNumber;
    }
}
