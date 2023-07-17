<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/16
 * Time: 3:21 PM
 */

namespace Eguana\CustomRMA\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Rma\Model\Rma\RmaDataMapper;

class UpdateRMAObserver implements ObserverInterface
{
    /**
     * @var Status
     */
    private $sourceStatus;

    /**
     * @param Status $status
     * @param RmaDataMapper $rmaDataMapper
     */
    public function __construct(
        Status $status
    ) {
        $this->sourceStatus = $status;
    }

    public function execute(Observer $observer)
    {
        /** @var \Eguana\CustomRMA\Model\Rma $rma */
        $rma = $observer->getRma();
        $itemStatuses = [];
        foreach ($rma->getItems() as $rmaItem) {
            $itemStatuses[] = $rmaItem->getData('status');
        }
        $rma->setStatus($this->sourceStatus->getStatusByItems($itemStatuses))->setIsUpdate(1);
    }
}
