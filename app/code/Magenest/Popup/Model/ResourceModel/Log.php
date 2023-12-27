<?php
namespace Magenest\Popup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init('magenest_log', 'log_id');
    }
}
