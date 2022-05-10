<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amore\Sales\Model\ResourceModel\Report\Order\Updatedat;

class Collection extends \Amore\Sales\Model\ResourceModel\Report\Order\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'sales_order_aggregated_updated';
}
