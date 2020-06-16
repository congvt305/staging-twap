<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 12:55 AM
 */

namespace Eguana\Magazine\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Main class to load data from db
 *
 * Class Magazine
 */
class Magazine extends AbstractDb
{

    /**
     * Constructer for this class
     */
    protected function _construct()
    {
        $this->_init('eguana_magazine', 'entity_id');
    }
}
