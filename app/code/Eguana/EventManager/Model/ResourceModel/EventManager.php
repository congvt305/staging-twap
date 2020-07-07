<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 4:00 PM
 */
namespace Eguana\EventManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Main class to load data from db
 *
 * Class EventManager
 */
class EventManager extends AbstractDb
{
    /**
     * Resource initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_event_manager', 'entity_id');
    }
}
