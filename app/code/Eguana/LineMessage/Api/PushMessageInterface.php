<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 26/11/20
 * Time: 4:17 PM
 */
namespace Eguana\LineMessage\Api;

/**
 * Interface PushMessageInterface
 *
 */
interface PushMessageInterface
{
    /**
     * GET for Post api
     * @param string $email
     * @param int $websiteId
     * @param string $message
     * @return string
     */
    public function linePushMessage($email, $websiteId, $message);
}
