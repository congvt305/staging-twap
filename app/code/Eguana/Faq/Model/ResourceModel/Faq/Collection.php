<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Model\ResourceModel\Faq;

use Eguana\Faq\Api\Data\FaqInterface;
use Eguana\Faq\Model\Faq;
use Eguana\Faq\Model\ResourceModel\AbstractCollection;
use Eguana\Faq\Model\ResourceModel\Faq as FaqResourceModel;

/**
 * Class Collection
 *
 * Eguana\Faq\Model\ResourceModel\Faq
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $eventPrefix = 'eguana_faq_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $eventObject = 'faq_collection';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);

        $this->performAfterLoad('eguana_faq_store', $entityMetadata->getLinkField());

        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Faq::class, FaqResourceModel::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Returns pairs block_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'title');
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $this->joinStoreRelationTable('eguana_faq_store', $entityMetadata->getLinkField());
    }
}
