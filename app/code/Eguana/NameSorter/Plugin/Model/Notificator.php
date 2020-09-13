<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/9/20
 * Time: 6:13 PM
 */
namespace Eguana\NameSorter\Plugin\Model;

use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\Notificator as NotificatorAlias;

/**
 * This class is used for the method which swap the First and Last Name
 * for Admin Panel forgot password
 * Class Notificator
 */
class Notificator
{
    /**
     * Before Plugin for sendForgotPassword method
     * This plugin is used to swap the First and Last Name
     * @param NotificatorAlias $subject
     * @param UserInterface $user
     * @return UserInterface[]
     */
    public function beforeSendForgotPassword(NotificatorAlias $subject, UserInterface $user)
    {
        $firstName = $user->getFirstName();
        $lastName  = $user->getLastName();
        $user->setFirstName($lastName);
        $user->setLastName($firstName);
        return [$user];
    }
}
