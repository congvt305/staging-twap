<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/25
 * Time: 10:02 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

class EcpayInvoice
{
    /**
     * 版本
     */
    const VERSION = '1.0.2002102';

    public $TimeStamp 	= '';
    public $MerchantID 	= '';
    public $HashKey 	= '';
    public $HashIV 		= '';
    public $Send 		= 'Send';
    public $Invoice_Method 	= 'INVOICE';		// 電子發票執行項目
    public $Invoice_Url 	= 'Invoice_Url';	// 電子發票執行網址
    /**
     * @var ECPayInvoiceSend
     */
    private $ECPayInvoiceSend;

    public function __construct(\Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceSend $ECPayInvoiceSend)
    {
        $this->Send = array(
            'RelateNumber' => '',
            'CustomerID' => '',
            'CustomerIdentifier' => '',
            'CustomerName' => '',
            'CustomerAddr' => '',
            'CustomerPhone' => '',
            'CustomerEmail' => '',
            'ClearanceMark' => '',
            'Print' => EcpayPrintMark::No,
            'Donation' => EcpayDonation::No,
            'LoveCode' => '',
            'CarruerType' => EcpayCarruerType::None,
            'CarruerNum' => '',
            'TaxType' => '',
            'SalesAmount' => '',
            'InvoiceRemark' => '',
            'Items' => array(),
            'InvType' => '',
            'vat' => EcpayVatType::Yes,
            'DelayFlag' => '',
            'DelayDay' => 0,
            'Tsr' => '',
            'PayType' => '',
            'PayAct' => '',
            'NotifyURL' => '',
            'InvoiceNo' => '',
            'AllowanceNotify' => '',
            'NotifyMail' => '',
            'NotifyPhone' => '',
            'AllowanceAmount' => '',
            'InvoiceNumber'  => '',
            'Reason'  => '',
            'AllowanceNo' => '',
            'Phone' => '',
            'Notify' => '',
            'InvoiceTag' => '',
            'Notified' => '',
            'BarCode' => '',
            'OnLine' => true
        );

        $this->TimeStamp = time();
        $this->ECPayInvoiceSend = $ECPayInvoiceSend;
    }

    public function Check_Out()
    {
        $arParameters = array_merge( array('MerchantID' => $this->MerchantID) , array('TimeStamp' => $this->TimeStamp), $this->Send);
        return $this->ECPayInvoiceSend->CheckOut($arParameters, $this->HashKey, $this->HashIV, $this->Invoice_Method, $this->Invoice_Url);
    }

    /**
     * 取得線上折讓單回傳資料
     *
     * @param  array $merchantInfo
     * @param  array $parameters
     * @return array
     */
    public function allowanceByCollegiateResponse($merchantInfo, $parameters)
    {
        $merchantInfo['method'] = ALLOWANCE_BY_COLLEGIATE ;
        return ecpayResponse::response($merchantInfo, $parameters);
    }
}
