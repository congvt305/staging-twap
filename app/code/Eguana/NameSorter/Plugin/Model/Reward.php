<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/9/20
 * Time: 11:18 AM
 */
namespace Eguana\NameSorter\Plugin\Model;

use Magento\Reward\Model\Reward as RewardAlias;

/**
 * This class is used for the plugins which swap the First and
 * Last Name for Reward points emails
 * Class Reward
 */
class Reward
{
    /**
     * Before Plugin for sendBalanceUpdateNotification method
     * @param RewardAlias $subject
     */
    public function beforeSendBalanceUpdateNotification(RewardAlias $subject)
    {
        $customer  = $subject->getCustomer();
        $firstName = $customer->getFirstname();
        $lastName  = $customer->getLastname();
        $customer->setFirstname($lastName);
        $customer->setLastname($firstName);
    }

    /**
     * Before Plugin for sendBalanceWarningNotification method
     * @param RewardAlias $subject
     * @param $item
     * @param $websiteId
     * @return array
     */
    public function beforeSendBalanceWarningNotification(RewardAlias $subject, $item, $websiteId)
    {
        $firstName = $item->getCustomerFirstname();
        $lastName  = $item->getCustomerLastname();
        $item->setCustomerFirstname($lastName);
        $item->setCustomerLastname($firstName);
        return [$item, $websiteId];
    }
}
