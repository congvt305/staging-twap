<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 20/4/20
 * Time: 5:11 PM
 */
namespace Eguana\SocialLogin\Model\ResourceModel\SocialLogin;

use Eguana\SocialLogin\Model\SocialLogin as SocialLogin;
use Eguana\SocialLogin\Model\ResourceModel\SocialLogin as SocialLoginResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollectionAlias;

/**
 * Class Collection
 *
 * Social login collection class
 */
class Collection extends AbstractCollectionAlias
{
    /**
     * @var string
     */
    protected $_idFieldName = 'sociallogin_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'sociallogin_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'sociallogin_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SocialLogin::class, SocialLoginResourceModel::class);
    }
}
