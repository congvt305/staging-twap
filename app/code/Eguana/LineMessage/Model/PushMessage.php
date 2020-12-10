<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 26/11/20
 * Time: 4:16 PM
 */
namespace Eguana\LineMessage\Model;

use Eguana\LineMessage\Api\PushMessageInterface;
use Eguana\LineMessage\Model\PushMessageHandler;

class PushMessage implements PushMessageInterface
{
    private $pushMessageHandler;

    public function __construct(
        PushMessageHandler $pushMessageHandler
    ) {
        $this->pushMessageHandler = $pushMessageHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function linePushMessage($email, $websiteId, $message)
    {
        try {
            $response = $this->pushMessageHandler->sendPushMessage($email, $websiteId, $message);
        } catch (\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
}
