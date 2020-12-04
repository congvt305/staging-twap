<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Eguana\CustomerBulletin\Model\Note as Model;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * model class for notes
 *
 * Class Note
 */
class Note extends AbstractDb
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * Note constructor.
     *
     * @param Context $context
     * @param DateTime $date
     */
    public function __construct(
        Context $context,
        DateTime $date
    ) {
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Resource initialisation
     */
    protected function _construct()
    {
        $this->_init('eguana_ticket_note', 'note_id');
    }

    /**
     * Before save callback
     *
     * @param AbstractModel|Model $object
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdateTime($this->date->gmtDate());
        if ($object->isObjectNew()) {
            $object->setCreationTime($this->date->gmtDate());
        }
        return parent::_beforeSave($object);
    }
}
