<?php

namespace Amasty\Feed\Model\Category\ResourceModel;

class Taxonomy extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_feed_google_taxonomy';

    public const ID_FIELD_NAME = 'id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}
