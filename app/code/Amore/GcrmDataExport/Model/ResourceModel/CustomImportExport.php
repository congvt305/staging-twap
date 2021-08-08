<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 6/7/21
 * Time: 4:45 PM
 */

namespace Amore\GcrmDataExport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model Class
 *
 * Class CustomImportExport
 */
class CustomImportExport extends AbstractDb
{
    /**
     * ResourceModel constructor
     **/
    protected function _construct()
    {
        $this->_init('eguana_gcrm_data_export_setting', 'id');
    }
}
