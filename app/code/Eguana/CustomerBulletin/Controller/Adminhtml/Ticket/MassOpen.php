<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Model\Ticket;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class MassDisable to open multiple records
 */
class MassOpen extends AbstractMassAction implements HttpPostActionInterface
{
    /**#@+*/
    const STATUS_OPEN = 1;
    const CUSTOMER_EMAIL_TEMPLATE = 'ticket_managment/email/customer_email_open';
    const ADMIN_EMAIL_TEMPLATE = 'ticket_managment/email/staff_email_open';
    /**#@-*/

    /**
     * @param Ticket $ticket
     * @return $this
     */
    protected function massAction(Ticket $ticket) : MassOpen
    {
        $ticket->setStatus(self::STATUS_OPEN);
        $this->ticketRepository->save($ticket);
        return $this;
    }
}
