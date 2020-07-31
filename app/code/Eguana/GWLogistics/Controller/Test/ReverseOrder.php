<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 5:36 AM
 */

namespace Eguana\GWLogistics\Controller\Test;

use Eguana\GWLogistics\Helper\Data;
use Eguana\GWLogistics\Model\Request\FamiCreateReverseShipmentOrder;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class ReverseOrder extends Action
{
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FamiCreateReverseShipmentOrder
     */
    private $createReverseShipmentOrder;

    public function __construct(
        Data $dataHelper,
        LoggerInterface $logger,
        FamiCreateReverseShipmentOrder $createReverseShipmentOrder,
        Context $context
    ) {
        parent::__construct($context);

        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->createReverseShipmentOrder = $createReverseShipmentOrder;
    }

    public function execute()
    {
        try {
            $Result = $this->createReverseShipmentOrder->execute();
//            $this->logger->debug('result: ', $Result);
            echo '<pre>' . print_r($Result, true) . '</pre>';
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
