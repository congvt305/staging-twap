<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/26/19
 * Time: 5:33 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model
 *
 * Class StoreInfo
 *  Eguana\StoreLocator\Model\ResourceModel
 */
class StoreInfo extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('storeinfo', 'entity_id');
    }
}
