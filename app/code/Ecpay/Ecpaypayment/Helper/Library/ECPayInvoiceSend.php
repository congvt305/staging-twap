<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/25
 * Time: 10:03 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

class ECPayInvoiceSend
{
    /**
     * @var ECPayIO
     */
    private $ECPayIO;
    /**
     * @var ECPayInvoiceValidator
     */
    private $ECPayInvoiceValidator;

    private $ECPay_Invoice_CheckMacValue;
    /**
     * @var ECPayEncryptType
     */
    private $ECPayEncryptType;
    /**
     * @var ECPayInvoiceVoidValidator
     */
    private $ECPayInvoiceVoidValidator;

    public function __construct(
        \Ecpay\Ecpaypayment\Helper\Library\ECPayIO $ECPayIO,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceValidator $ECPayInvoiceValidator,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceVoidValidator $ECPayInvoiceVoidValidator,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPay_Invoice_CheckMacValue,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayEncryptType $ECPayEncryptType
    ) {
        $this->ECPayIO = $ECPayIO;
        $this->ECPayInvoiceValidator = $ECPayInvoiceValidator;
        $this->ECPay_Invoice_CheckMacValue = $ECPay_Invoice_CheckMacValue;
        $this->ECPayEncryptType = $ECPayEncryptType;
        $this->ECPayInvoiceVoidValidator = $ECPayInvoiceVoidValidator;
    }

    // 發票物件
    public $InvoiceObj ;
    public $InvoiceObj_Return ;

    /**
     * 背景送出資料
     */
    function CheckOut($arParameters = array(), $HashKey='', $HashIV='', $Invoice_Method = '', $ServiceURL='')
    {

        // 發送資訊處理
        $arParameters = $this->process_send($arParameters, $HashKey, $HashIV, $Invoice_Method, $ServiceURL);

        $szResult = $this->ECPayIO->ServerPost($arParameters, $ServiceURL);

        // 回傳資訊處理
        $arParameters_Return = $this->process_return($szResult, $HashKey, $HashIV, $Invoice_Method);

        return $arParameters_Return ;
    }

    // 資料檢查與過濾(送出)
    protected function process_send($arParameters = array(), $HashKey = '', $HashIV = '', $Invoice_Method = '', $ServiceURL = '')
    {
        //宣告物件
//        $InvoiceMethod    = 'ECPay_'.$Invoice_Method;
        if ($Invoice_Method === 'INVOICE') {
            $this->InvoiceObj = $this->ECPayInvoiceValidator;
        } elseif ($Invoice_Method === 'INVOICE_VOID') {
            $this->InvoiceObj = $this->ECPayInvoiceVoidValidator;
        }

        // 1寫入參數
        $arParameters = $this->InvoiceObj->insert_string($arParameters);

        // 2檢查共用參數
        $this->check_string($arParameters['MerchantID'], $HashKey, $HashIV, $Invoice_Method, $ServiceURL);

        // 3檢查各別參數
        $arParameters = $this->InvoiceObj->check_extend_string($arParameters);

        // 4處理需要轉換為urlencode的參數
        $arParameters = $this->urlencode_process($arParameters, $this->InvoiceObj->urlencode_field);

        // 5欄位例外處理方式(送壓碼前)
        $arException = $this->InvoiceObj->check_exception($arParameters);

        // 6產生壓碼
        $arParameters['CheckMacValue'] = $this->generate_checkmacvalue($arException, $this->InvoiceObj->none_verification, $HashKey, $HashIV);

        return $arParameters ;
    }

    /**
     * 資料檢查與過濾(回傳)
     */
   function process_return($sParameters = '', $HashKey = '', $HashIV = '', $Invoice_Method = '')
    {

        //宣告物件
//        $InvoiceMethod    = 'ECPay_'.$Invoice_Method;
        if ($Invoice_Method === 'INVOICE') {
            $this->InvoiceObj_Return = $this->ECPayInvoiceValidator;
        } elseif ($Invoice_Method === 'INVOICE_VOID') {
            $this->InvoiceObj_Return = $this->ECPayInvoiceVoidValidator;
        }
        $this->ECPayInvoiceValidator_Return = $this->ECPayInvoiceValidator;

        // 7字串轉陣列
        $arParameters = $this->string_to_array($sParameters);

        // 8欄位例外處理方式(送壓碼前)
        $arException = $this->InvoiceObj_Return->check_exception($arParameters);

        // 9產生壓碼(壓碼檢查)
        if(isset($arParameters['CheckMacValue'])){
            $CheckMacValue = $this->generate_checkmacvalue($arException, $this->InvoiceObj_Return->none_verification, $HashKey, $HashIV);

            if($CheckMacValue != $arParameters['CheckMacValue']){
                throw new \Exception('注意：壓碼錯誤');
            }
        }

        // 10處理需要urldecode的參數
        $arParameters = $this->urldecode_process($arParameters, $this->InvoiceObj_Return->urlencode_field);

        return $arParameters ;
    }

    /**
     * 2檢查共同參數
     */
    protected  function check_string($MerchantID = '', $HashKey = '', $HashIV = '', $Invoice_Method = 'INVOICE', $ServiceURL = '')
    {

        $arErrors = array();

        // 檢查是否傳入動作方式
        if($Invoice_Method == '' || $Invoice_Method == 'Invoice_Method') {
            array_push($arErrors, 'Invoice_Method is required.');
        }

        // 檢查是否有傳入MerchantID
        if(strlen($MerchantID) == 0) {
            array_push($arErrors, 'MerchantID is required.');
        }

        if(strlen($MerchantID) > 10) {
            array_push($arErrors, 'MerchantID max langth as 10.');
        }

        // 檢查是否有傳入HashKey
        if(strlen($HashKey) == 0) {
            array_push($arErrors, 'HashKey is required.');
        }

        // 檢查是否有傳入HashIV
        if(strlen($HashIV) == 0) {
            array_push($arErrors, 'HashIV is required.');
        }

        // 檢查是否有傳送網址
        if(strlen($ServiceURL) == 0) {
            array_push($arErrors, 'Invoice_Url is required.');
        }

        if(sizeof($arErrors)>0) throw new Exception(join('<br>', $arErrors));
    }

    /**
     * 4處理需要轉換為urlencode的參數
     */
    protected  function urlencode_process($arParameters = array(), $urlencode_field = array())
    {
        foreach($arParameters as $key => $value) {

            if(isset($urlencode_field[$key])) {
                $arParameters[$key] = urlencode($value);
                $arParameters[$key] = $this->ECPay_Invoice_CheckMacValue->ReplaceSymbol($arParameters[$key]);
            }
        }

        return $arParameters ;
    }

    /**
     * 6,9產生壓碼
     */
    protected  function generate_checkmacvalue($arParameters = array(), $none_verification = array(), $HashKey = '', $HashIV = '')
    {

        $sCheck_MacValue = '';

        // 過濾不需要壓碼的參數
        foreach($none_verification as $key => $value) {
            if(isset($arParameters[$key])) {
                unset($arParameters[$key]) ;
            }
        }

        $sCheck_MacValue = $this->ECPay_Invoice_CheckMacValue->generate($arParameters, $HashKey, $HashIV, $this->ECPayEncryptType::ENC_MD5);

        return $sCheck_MacValue ;
    }

    /**
     * 7 字串轉陣列
     */
    protected  function string_to_array($Parameters = '')
    {

        $aParameters 	 = array();
        $aParameters_Tmp = array();

        $aParameters_Tmp  = explode('&', $Parameters);

        foreach($aParameters_Tmp as $part) {
            list($paramName, $paramValue) = explode('=', $part, 2);
            $aParameters[$paramName] = $paramValue ;
        }

        return $aParameters ;
    }

    /**
     * 10處理urldecode的參數
     */
    protected  function urldecode_process($arParameters = array(), $urlencode_field = array())
    {
        foreach($arParameters as $key => $value) {
            if(isset($urlencode_field[$key])) {
                $arParameters[$key] = $this->ECPay_Invoice_CheckMacValue->ReplaceSymbolDecode($arParameters[$key]);
                $arParameters[$key] = urldecode($value);
            }
        }

        return $arParameters ;
    }
}
