<?php

namespace Amasty\Feed\Model\ValidProduct\ResourceModel;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class ValidProduct extends AbstractDb
{
    public const TABLE_NAME = 'amasty_feed_valid_products';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ValidProductsInterface::ENTITY_ID);
    }
}
