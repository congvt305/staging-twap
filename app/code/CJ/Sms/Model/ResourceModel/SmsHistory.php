<?php
declare(strict_types=1);

namespace CJ\Sms\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SmsHistory  extends AbstractDb
{

    /**
     * Resource initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cj_sms_history', 'entity_id');
    }

    /**
     * Delete all data
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteAllData()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
