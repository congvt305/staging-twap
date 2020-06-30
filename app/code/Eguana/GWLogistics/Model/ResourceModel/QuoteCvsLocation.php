<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 9:29 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuoteCvsLocation extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('eguana_gwlogistics_quote_cvs_location', QuoteCvsLocationInterface::LOCATION_ID);
    }
}
