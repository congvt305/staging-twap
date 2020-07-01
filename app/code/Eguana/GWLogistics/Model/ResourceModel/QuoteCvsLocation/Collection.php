<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 9:36 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eguana\GWLogistics\Model\QuoteCvsLocation::class, \Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation::class);
        $this->setMainTable('eguana_gwlogistics_quote_cvs_location');
    }

}
