<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 27/10/20
 * Time: 2:17 PM
 */
namespace Eguana\Redemption\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Redemption Counter Model
 *
 * Class Counter
 */
class Counter extends AbstractDb
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
        $this->_init('eguana_redemption_user', 'entity_id');
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
