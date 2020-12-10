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
 * Class MassClose to close multiple records
 */
class MassClose extends AbstractMassAction implements HttpPostActionInterface
{

    /**#@+*/
    const STATUS_CLOSE = 0;
    const CUSTOMER_EMAIL_TEMPLATE = 'ticket_managment/email/customer_email_close';
    const ADMIN_EMAIL_TEMPLATE = 'ticket_managment/email/staff_email_close';
    /**#@-*/

    /**
     * @param Ticket $ticket
     * @return $this
     */
    protected function massAction(Ticket $ticket) : MassClose
    {
        $ticket->setStatus(self::STATUS_CLOSE);
        $this->ticketRepository->save($ticket);
        return $this;
    }
}
