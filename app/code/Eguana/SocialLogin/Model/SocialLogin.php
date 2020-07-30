<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: silentarmy
 * Date: 20/4/20
 * Time: 5:06 PM
 */
namespace Eguana\SocialLogin\Model;

use Eguana\SocialLogin\Api\Data\SocialLoginInterface;
use Eguana\SocialLogin\Model\ResourceModel\SocialLogin as SocialLoginResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SocialLogin
 *
 * SocialLogin Model table
 */
class SocialLogin extends AbstractModel implements SocialLoginInterface, IdentityInterface
{
    const CACHE_TAG = 'eguana_sociallogin_customer';
    /**
     * @var string
     */
    protected $_cacheTag = 'eguana_sociallogin_customer';

    /**
     * @var string
     */
    protected $_eventPrefix = 'eguana_sociallogin_customer';

    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->_init(SocialLoginResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setSocialloginId($id)
    {
        return $this->setData(self::SOCIALLOGIN_ID, $id);
    }

    /**
     * @return int
     */
    public function getSocialloginId()
    {
        return $this->getData(self::SOCIALLOGIN_ID);
    }

    /**
     * @return string
     */
    public function getSocialId()
    {
        return $this->getData(self::SOCIAL_ID);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getData(self::USERNAME);
    }

    /**
     * @return string
     */
    public function getSocialmedia()
    {
        return $this->getData(self::SOCIALMEDIA);
    }
}
