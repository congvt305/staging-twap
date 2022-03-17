<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Payoo\PayNow\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

class AuthorizationRequest implements BuilderInterface
{

    private $config;
    private $url;

    public function __construct(
        ConfigInterface $config,
        UrlInterface $url
    ) {
        $this->config = $config;
        $this->url = $url;
    }

    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $data = $this->buildData($order);

        return [
            'checkout_url' => $this->config->getValue(
                'checkout_url',
                $order->getStoreId()
            ),
            'data' => $data['data'],
            'checksum' => $data['checksum'],
            'refer' => $data['refer']
        ];
    }

    private function buildData($order) {
        $order_ship_date 	=  	date('d/m/Y');
		$order_ship_days 	= 	'1';
		$business = nl2br($this->config->getValue('business',$order->getStoreId()));
		$order_id = nl2br($order->getOrderIncrementId());
		$total_amount= $this->formatPrice($order->getGrandTotalAmount());
		$url_success = $this->url->getUrl('payoo/payment/status');
		$notify_url =  $this->url->getUrl('payoo/notification/index');
		$shop_id = nl2br($this->config->getValue('shop_id',$order->getStoreId()));
		$shop_title = nl2br($this->config->getValue('shop_title',$order->getStoreId()));
		$shop_domain =nl2br($this->config->getValue('shop_domain',$order->getStoreId()));
		$checksum_key =nl2br($this->config->getValue('checksum_key',$order->getStoreId()));
		
		$validity_time =  date('YmdHis', strtotime('+1 day', time())); // payemnt expire date
		
		$order_description	=	'Thanh toan cho ma don hang: '.$order_id.'. Mua tai website: '.$shop_domain; 
		
		$str = $this->createRequestUrl($business, $shop_id, $shop_title, $shop_domain, $order_id, $order_ship_date, $order_ship_days, $total_amount, $order_description, $url_success, $notify_url,$validity_time);
        
        $checksum = hash('sha512',$checksum_key.$str);//sha1($checksum_key.$str);

		return 	['data' => $str, 'checksum' => $checksum, 'refer' => $shop_domain];
    }

    private function createRequestUrl($business, $shop_id, $shop_title, $shop_domain, $order_no, $order_ship_date, $order_ship_days, $order_cash_amount, $order_description, $shop_back_url, $notify_url,$validity_time)
	{
        $str='<shops><shop><session>'.$order_no.'</session><username>'.$business.'</username><shop_id>'.$shop_id.'</shop_id><shop_title>'.$shop_title.'</shop_title><shop_domain>'.$shop_domain.'</shop_domain><shop_back_url>'.$shop_back_url.'</shop_back_url><order_no>'.$order_no.'</order_no><order_cash_amount>'.$order_cash_amount.'</order_cash_amount><order_ship_date>'.$order_ship_date.'</order_ship_date><order_ship_days>'.$order_ship_days.'</order_ship_days><order_description>'.urlencode($order_description).'</order_description><notify_url>'.$notify_url.'</notify_url><validity_time>'.$validity_time.'</validity_time><JsonResponse>true</JsonResponse></shop></shops>';
        return $str;
	}

    private function formatPrice($price) {
        $price_bk	= strip_tags($price);
        $price_bk 	= str_replace(',','',$price_bk);
        $price_bk 	= str_replace('.','',$price_bk);
        $price_bk 	= str_replace('VNĐ','',$price_bk);
        $price_bk 	= trim($price_bk);
        return $price_bk;
     
    }
	
}
