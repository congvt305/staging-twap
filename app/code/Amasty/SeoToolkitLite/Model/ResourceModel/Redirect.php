<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\ResourceModel;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Redirect extends AbstractDb
{
    public function _construct()
    {
        $this->_init(RedirectInterface::TABLE_NAME, RedirectInterface::REDIRECT_ID);
    }

    /**
     * @param AbstractModel $object
     * @return $this|Redirect
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(AbstractModel $object)
    {
        parent::save($object);
        $storeIds = $object->getStoreIds();
        
        if ($storeIds) {
            $storeTable = $this->getTable(RedirectInterface::STORE_TABLE_NAME);
            $this->getConnection()->delete($storeTable, ['redirect_id = ?' => $object->getRedirectId()]);
            $data = [];
            foreach ($storeIds as $storeId) {
                $data[] = [
                    RedirectInterface::REDIRECT_ID => $object->getRedirectId(),
                    RedirectInterface::STORE_ID => $storeId
                ];
            }
            $this->getConnection()->insertMultiple($this->getTable(RedirectInterface::STORE_TABLE_NAME), $data);
        }
        
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $tableName = $this->getTable(RedirectInterface::TABLE_NAME);
        $select->joinLeft(
            ['stores' => $this->getTable(RedirectInterface::STORE_TABLE_NAME)],
            sprintf('%s.redirect_id = stores.redirect_id', $tableName),
            ['GROUP_CONCAT(store_id SEPARATOR ",") as store_ids']
        )->group($tableName . '.redirect_id');

        return $select;
    }
}
