<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 1:07 PM
 *
 */

namespace Eguana\BizConnect\Model\ResourceModel\LoggedOperation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Eguana\BizConnect\Model\LoggedOperation::class,
            \Eguana\BizConnect\Model\ResourceModel\LoggedOperation::class
        );
        $this->setMainTable('eguana_bizconnect_logged_operation');
    }
}
