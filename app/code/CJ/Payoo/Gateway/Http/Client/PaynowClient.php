<?php
declare(strict_types=1);

namespace CJ\Payoo\Gateway\Http\Client;

use Magento\Framework\Registry;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class PaynowClient extends \Payoo\PayNow\Gateway\Http\Client\PaynowClient
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     * @param Registry $registry
     */
    public function __construct(
        Logger $logger,
        Registry $registry
    ) {
        parent::__construct($logger);
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * Customize add method tab for url
     *
     * @param TransferInterface $transferObject
     * @return array|mixed
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        //customize add method
        $methodTab = $this->registry->registry('method_tab');

        $data = $transferObject->getBody();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data['checkout_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['data' => $data['data'], 'checksum' => $data['checksum'], 'refer' => $data['refer'], 'method' => $methodTab]);
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
