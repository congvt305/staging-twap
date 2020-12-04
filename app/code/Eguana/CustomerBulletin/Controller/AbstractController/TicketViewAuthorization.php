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

use Magento\Customer\Model\Session;
use Eguana\CustomerBulletin\Model\Ticket;

/**
 * Check if ticket can be viewed by customer
 *
 * Class TicketViewAuthorization
 */
class TicketViewAuthorization implements TicketViewAuthorizationInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * get permission Customer can view this ticket
     * @param Ticket $ticket
     * @return bool
     */
    public function canView(Ticket $ticket)
    {
        $customerId = $this->customerSession->getCustomerId();
        if ($ticket->getId()
            && $ticket->getCustomerId()
            && $ticket->getCustomerId() == $customerId
        ) {
            return true;
        }
        return false;
    }
}
