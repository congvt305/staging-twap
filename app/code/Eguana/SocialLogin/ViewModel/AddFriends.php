<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/8/20
 * Time: 5:23 PM
 */
namespace Eguana\SocialLogin\ViewModel;

use Eguana\SocialLogin\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AddFriends
 * Get line add friends link
 */
class AddFriends implements ArgumentInterface
{

    /**
     * @var Data
     */
    private $helperData;

    /**
     * AddFriends constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData= $helperData;
    }

    /**
     * Get line add friends link
     * @return mixed
     */
    public function lineAddFriendLink()
    {
        return $this->helperData->getLineAddFriendLink();
    }

    /**
     * Check if module is enabled or not
     * @return bool
     */
    public function getEnabledInFrontend()
    {
        return $this->helperData->isEnabledInFrontend();
    }

    /**
     * Check if line login is enbled or not
     * @return bool
     */
    public function getEnabledLine()
    {
        return $this->helperData->isEnabledLine();
    }

    /**
     * Check if facebook login is enbled or not
     * @return bool
     */
    public function getEnabledFacebook()
    {
        return $this->helperData->isEnabledFacebook();
    }

    /**
     * Get facebook add friends link
     * @return mixed
     */
    public function facebookAddFriendLink()
    {
        return $this->helperData->getFacebookAddFriendLink();
    }

    /**
     * Get store identifier
     * @return  int
     */
    public function getStoreId()
    {
        return $this->helperData->getStoreId();
    }
}
