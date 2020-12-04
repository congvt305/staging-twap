<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Model\ResourceModel\Ticket;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Eguana\CustomerBulletin\Model\Ticket as TicketModel;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket as ResourceModelTicket;

/**
 * this class is used for the ticket colletion
 *
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'ticket_id';

    /**
     * Collection initialisation
     */
    protected function _construct()
    {
        $this->_init(TicketModel::class, ResourceModelTicket::class);
    }
}
