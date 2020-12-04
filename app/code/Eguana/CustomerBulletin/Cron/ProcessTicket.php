<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Shahroz
 * Date: 11/15/19
 * Time: 1:09 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Cron;

use Eguana\CustomerBulletin\Model\TicketCloser;

/**
 * This class is use to change the status of the class
 * Class ProcessTicket
 */
class ProcessTicket
{
    /**
     * @var $ticketCloser
     */
    private $ticketCloser;

    /**
     * ProcessTicket constructor.
     * @param TicketCloser $ticketCloser
     */
    public function __construct(
        TicketCloser $ticketCloser
    ) {
        $this->ticketCloser = $ticketCloser;
    }

    /**
     * this function is use to update ticket status
     * @return void
     */
    public function execute() : void
    {
        $this->ticketCloser->closeTicket();
    }
}
