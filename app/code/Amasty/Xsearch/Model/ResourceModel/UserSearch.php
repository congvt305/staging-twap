<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\Xsearch\Model\ResourceModel;

class UserSearch extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'amasty_xsearch_users_search';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
