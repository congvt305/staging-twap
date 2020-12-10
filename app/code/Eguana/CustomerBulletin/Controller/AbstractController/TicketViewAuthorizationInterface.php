<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
namespace Eguana\CustomerBulletin\Controller\AbstractController;

use Eguana\CustomerBulletin\Model\Ticket;

/**
 * Interface \Eguana\CustomerBulletin\Controller\AbstractController\TicketViewAuthorizationInterface
 *
 */
interface TicketViewAuthorizationInterface
{
    /**
     * Check if ticket can be viewed by customer
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function canView(Ticket $ticket);
}
