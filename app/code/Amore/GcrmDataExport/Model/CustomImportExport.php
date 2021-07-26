<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 6/7/21
 * Time: 4:45 PM
 */

namespace Amore\GcrmDataExport\Model;

use Magento\Framework\Model\AbstractModel;
use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport as ResourceModel;

/**
 * Class CustomImportExport
 */
class CustomImportExport extends AbstractModel
{
    /**
     * Model constructor
     **/
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
