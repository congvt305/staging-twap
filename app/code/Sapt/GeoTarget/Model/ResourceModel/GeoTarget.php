<?php
namespace Sapt\GeoTarget\Model\ResourceModel;

class GeoTarget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sapt_geo_target','entity_id');
    }

    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $entityId = $object->getOrigData('entity_id');

        if(!empty($entityId)) {
            $this->getConnection()->delete($this->getTable('sapt_geo_target_store'),
                ['link_id = ?' => $entityId]
            );
        }

        return parent::_afterDelete($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $storeIds = $object->getData('store_ids');
        if ($storeIds && ($storeIds != $object->getOrigData('store_ids'))) {
            if(!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }

            $this->getConnection()->delete($this->getTable('sapt_geo_target_store'),
                ['link_id = ?' => $object->getEntityId()]
            );

            $dataForInsert = [];
            foreach ($storeIds as $storeId) {
                $dataForInsert[] = [
                    'link_id' => $object->getEntityId(),
                    'store_id' => $storeId
                ];
            }
            $this->getConnection()->insertOnDuplicate($this->getTable('sapt_geo_target_store'), $dataForInsert, []);
        }
        return parent::_afterSave($object);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getEntityId()) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('sapt_geo_target_store'), 'store_id')
                ->where('link_id = ?', $object->getEntityId());
            $object->setData('store_ids', $this->getConnection()->fetchCol($select));
        }

        return parent::_afterLoad($object);
    }
}
