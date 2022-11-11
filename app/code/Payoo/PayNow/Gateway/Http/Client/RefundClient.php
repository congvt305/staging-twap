<?php

namespace Payoo\PayNow\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Payoo\PayNow\Logger\Logger as PayooLogger;
use Magento\Framework\HTTP\Client\Curl;

class RefundClient implements ClientInterface
{
    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var PayooLogger
     */
    private PayooLogger $logger;

    /**
     * @param Curl $curl
     * @param PayooLogger $logger
     */
    public function __construct(
        Curl $curl,
        PayooLogger $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();

        $params = ['RequestData' => $data['RequestData'], 'Signature' => $data['Signature']];
        //set curl options
        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        //set curl header
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("apipassword", $data['apipassword']);
        $this->curl->addHeader("apisignature", $data['apisignature']);
        $this->curl->addHeader("apiusername", $data['apiusername']);

        $this->curl->post($data['refund_url'], json_encode($params));
        $response = $this->curl->getBody();
        $responseFormat = json_decode($response, true);
        $this->logger->info(PayooLogger::TYPE_LOG_REFUND,
            [
                'request' => $data,
                'response' => $responseFormat
            ]);
        return $responseFormat;
    }
}
