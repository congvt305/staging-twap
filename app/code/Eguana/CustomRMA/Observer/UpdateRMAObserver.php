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
use Magento\Rma\Model\Rma\Source\StatusFactory;
use Magento\Rma\Model\Rma\RmaDataMapperFactory;

class UpdateRMAObserver implements ObserverInterface
{
    /**
     * @var Status
     */
    private $sourceStatus;

    /**
     * @var $rmaDataMapper
     */
    private $rmaDataMapper;

    /**
     * @param Status $status
     * @param RmaDataMapper $rmaDataMapper
     */
    public function __construct(
        Status $status,
        RmaDataMapper $rmaDataMapper
    ) {
        $this->sourceStatus = $status;
        $this->rmaDataMapper = $rmaDataMapper;
    }

    public function execute(Observer $observer)
    {
        /** @var \Eguana\CustomRMA\Model\Rma $rma */
        $rma = $observer->getRma();
        $items = [];
        foreach ($rma->getItems() as $rmaItem) {
            $items[] = $rmaItem->getData();
        }
        $itemStatuses = $this->rmaDataMapper->create()->combineItemStatuses($items, $rma->getId());
        $rma->setStatus($this->sourceStatus->create()->getStatusByItems($itemStatuses)->setIsUpdate(1));
    }
}
