<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 10:23 AM
 *
 */

namespace Eguana\BizConnect\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime;

class LoggedOperation extends AbstractModel
{
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    protected function _construct()
    {
        $this->_init(\Eguana\BizConnect\Model\ResourceModel\LoggedOperation::class);
    }

}

