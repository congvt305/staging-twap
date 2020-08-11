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
 * Class LineAddFriends
 *
 * Get line add friends link
 */
class LineAddFriends implements ArgumentInterface
{

    /**
     * @var Data
     */
    private $helperData;

    /**
     * LineAddFriends constructor.
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
}
