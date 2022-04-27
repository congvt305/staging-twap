<?php

namespace Payoo\PayNow\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class PaynowClient implements ClientInterface
{
  
    private $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
   
        $data = $transferObject->getBody();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data['checkout_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['data' => $data['data'], 'checksum' => $data['checksum'], 'refer' => $data['refer']]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
      
        $err = curl_error($ch);
        curl_close($ch);

        $response = json_decode($res, true);

        $this->logger->debug(
            [
                'request' => $data,
                'response' => $response,
            ]
        );

        return $response;
    }
}
