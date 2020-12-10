<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 26/11/20
 * Time: 5:18 PM
 */
namespace Eguana\LineMessage\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Eguana\LineMessage\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PushMessageHandler
 *
 * Send push message
 */
class PushMessageHandler
{
    const LINE_API = 'https://api.line.me/v2/bot/message/push';

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customer;

    /**
     * PushMessageHandler constructor.
     * @param Curl $curl
     * @param SerializerInterface $serializer
     * @param Data $helper
     * @param CustomerRepositoryInterface $customer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        SerializerInterface $serializer,
        Data $helper,
        CustomerRepositoryInterface $customer,
        LoggerInterface $logger
    ) {
        $this->curlClient                        = $curl;
        $this->serializer                        = $serializer;
        $this->helper                            = $helper;
        $this->customer                          = $customer;
        $this->logger                            = $logger;
    }

    /**
     * Send push message
     * @param $email
     * @param $websiteId
     * @param $message
     * @return array|bool|float|int|string|null
     * @throws NoSuchEntityException
     */
    public function sendPushMessage($email, $websiteId, $message)
    {
        $customer = null;
        try {
            $customer = $this->customer->get($email, $websiteId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Requested customer doesn\'t exist'));
        }
        if ($customer) {
            $accessToken = $this->helper->getLineAccessToken($websiteId);
            $data = [];
            $to = null;
            $lineMessageAgreement = null;
            try {
                $to = $customer->getCustomAttribute('line_id')->getValue();
            } catch (\Exception $e) {
                throw new NoSuchEntityException(__('Requested customer has no LINE Account.'));
            }
            try {
                $lineMessageAgreement = $customer->getCustomAttribute('line_message_agreement')->getValue();
            } catch (\Exception $e) {
                throw new NoSuchEntityException(__('Requested customer has no LINE Message Agreement.'));
            }
            if (!$lineMessageAgreement) {
                throw new NoSuchEntityException(__('Requested customer has no LINE Message Agreement.'));
            }
            if (!$to) {
                throw new NoSuchEntityException(__('Requested customer has no LINE Account.'));
            }
            $data["to"] = $to;
            $messageData["type"] = "text";
            $messageData["text" ] = $message;
            $data["messages"][] = $messageData;
            $request = $this->serializer->serialize($data);
            $this->curlClient->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => $request,
                    CURLOPT_HEADER => false,
                    CURLOPT_VERBOSE => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer {'.$accessToken.'}'
                    ]
                ]
            );
            $this->curlClient->post(self::LINE_API, []);
            return $this->serializer->unserialize($this->curlClient->getBody());
        }
        return null;
    }
}
