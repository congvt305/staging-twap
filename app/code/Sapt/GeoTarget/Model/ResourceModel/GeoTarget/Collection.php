<?php
namespace Sapt\GeoTarget\Model\ResourceModel\GeoTarget;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Sapt\GeoTarget\Model\GeoTarget','Sapt\GeoTarget\Model\ResourceModel\GeoTarget');
    }

    public function addStoreIdFilter(array $storeId)
    {
        $this->getSelect()
            ->joinInner(
                ['sapt_geo_target_store' => $this->getTable('sapt_geo_target_store')],
                'main_table.entity_id = sapt_geo_target_store.link_id',
                []
            )->where('sapt_geo_target_store.store_id IN(?) ', $storeId);
        return $this;
    }

    public function getStoreIdWithPathFilter($storeId, $path)
    {
        $storeId = (int)$storeId;
        if ($storeId < 0 || empty($path)) {
            return '';
        }
        $storeIds = [$storeId, 0];

        $this->getSelect()
            ->joinInner(
                ['sapt_geo_target_store' => $this->getTable('sapt_geo_target_store')],
                'main_table.entity_id = sapt_geo_target_store.link_id',
                []
            )->where('sapt_geo_target_store.store_id IN(?) ', $storeIds)
            ->where('main_table.page_path = ? ', $path)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'sapt_geo_target_store.store_id as store_id',
                'main_table.geo_tag'
            ])->order('sapt_geo_target_store.store_id DESC');
        $data = $this->getFirstItem()->getData();
        return $data;
    }

    protected function _afterLoad()
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('sapt_geo_target_store'), 'link_id')
            ->columns(['store_ids' => new \Zend_Db_Expr('GROUP_CONCAT(store_id SEPARATOR ",")')])
            ->group('link_id');
        $storeData = $this->getConnection()->fetchAll($select);
        foreach ($storeData as $data) {
            if ($item = $this->getItemById($data['link_id'])) {
                $item->setStoreIds(explode(',', $data['store_ids']));
            }

        }
        return parent::_afterLoad();
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreIdFilter([(int)$condition['eq']]);
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
