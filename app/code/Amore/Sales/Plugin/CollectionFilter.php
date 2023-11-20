<?php
declare(strict_types=1);

namespace Amore\Sales\Plugin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CollectionFilter extends \Magento\AdminGws\Plugin\CollectionFilter
{
    /**
     * Adds allowed websites or stores to query filter.
     *
     * @param AbstractCollection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function beforeLoadWithFilter(AbstractCollection $collection, $printQuery = false, $logQuery = false)
    {
        if ($collection instanceof \Magento\Sales\Model\ResourceModel\Report\Order\Collection) {
            return [$printQuery, $logQuery];
        } else {
            parent::beforeLoadWithFilter($collection, $printQuery, $logQuery);
        }
    }
}
