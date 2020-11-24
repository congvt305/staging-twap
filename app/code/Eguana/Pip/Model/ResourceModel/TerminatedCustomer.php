<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 7:20 PM
 */
namespace Eguana\Pip\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Terminated Customer Resource Model
 *
 * Class TerminatedCustomer
 */
class TerminatedCustomer extends AbstractDb
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * TerminatedCustomer constructor.
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
        $this->_init('eguana_pip_terminated_customer', 'entity_id');
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
