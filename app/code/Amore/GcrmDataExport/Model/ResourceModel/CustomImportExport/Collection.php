<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 6/7/21
 * Time: 4:50 PM
 */

namespace Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amore\GcrmDataExport\Model\CustomImportExport as Model;
use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport as ResourceModel;

/**
 * This class returns collection of any entity
 *
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'custom_export_event_prefix';
    protected $_eventObject = 'custom_export_collection';

    /**
     * Collection class constructor
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
