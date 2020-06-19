<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 16/6/20
 * Time: 6:11 PM
 */

namespace Eguana\SocialLogin\Api\Data;

/**
 * SocialLogin SocialLoginInterface interface.
 *
 * @api
 */
interface SocialLoginInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SOCIALLOGIN_ID = 'sociallogin_id';

    const SOCIAL_ID = 'social_id';

    const USERNAME = 'username';

    const SOCIALMEDIA = 'socialmedia';

    const CUSTOMER_ID = 'customer_id';

    /**
     * Get Social Login ID.
     *
     * @return int|null
     */
    public function getSocialloginId();

    /**
     * Set Social Login ID.
     * @param $id
     * @return mixed
     */
    public function setSocialloginId($id);

    /**
     * get social id
     *
     * @return int|null
     */
    public function getSocialId();

    /**
     * get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * get username
     * @return mixed
     */
    public function getUsername();

    /**
     * get social media
     * @return mixed
     */
    public function getSocialmedia();
}
