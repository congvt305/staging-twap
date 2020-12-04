<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Model\ResourceModel\Note;

use Eguana\CustomerBulletin\Model\Note as NoteModel;
use Eguana\CustomerBulletin\Model\ResourceModel\Note as ResourceModelNote;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * collection for the notes
 *
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'note_id';

    /**
     * Collection initialisation
     */
    protected function _construct()
    {
        $this->_init(NoteModel::class, ResourceModelNote::class);
    }
}
