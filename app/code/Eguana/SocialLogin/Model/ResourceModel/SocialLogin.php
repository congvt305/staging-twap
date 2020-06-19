<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: silentarmy
 * Date: 20/4/20
 * Time: 5:08 PM
 */
namespace Eguana\SocialLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SocialLogin
 *
 * Resrouce model for sociallogin customer table
 */
class SocialLogin extends AbstractDb
{

    /**
     * Initilization
     */
    protected function _construct()
    {
        $this->_init('eguana_sociallogin_customer', 'sociallogin_id');
    }
}
