<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 1:05 PM
 *
 */

namespace Eguana\BizConnect\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LoggedOperation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('eguana_bizconnect_logged_operation', 'id');
    }
}
