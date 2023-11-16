<?php

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\Data\SapSyncStockBodyInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapSyncStockBody  extends AbstractExtensibleModel implements SapSyncStockBodyInterface
{

    /**
     * @inheritDoc
     */
    public function getStockData()
    {
        return $this->getData('stockData');
    }

    /**
     * @inheritDoc
     */
    public function setStockData($stockData)
    {
        return $this->setData('stockData', $stockData);
    }
}
