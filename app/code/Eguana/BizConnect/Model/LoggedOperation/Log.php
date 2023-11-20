<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 12:35 PM
 *
 */

namespace Eguana\BizConnect\Model\LoggedOperation;

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Eguana\BizConnect\Model\ResourceModel\LoggedOperation\Log::class);
    }
}

