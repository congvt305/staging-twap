<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * RedInvoice Model
 *
 * Class RedInvoice
 */
class RedInvoice extends AbstractDb
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
        $this->_init('eguana_red_invoice_data', 'id');
    }

    /**
     * Before save callback
     *
     * @param AbstractModel $object
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
