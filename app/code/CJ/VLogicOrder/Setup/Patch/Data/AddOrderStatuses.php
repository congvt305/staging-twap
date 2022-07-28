<?php

namespace CJ\VLogicOrder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AddDeliveryCompleteOrderStatus
 *
 * To add new order status "delivery_complete"
 */
class AddOrderStatuses implements DataPatchInterface
{
    /**#@+
     * Constants for status
     */
    const STATUS_CODE_PREPARING = 'preparing';
    const STATUS_LABEL_PREPARING = 'Preparing';

    const STATUS_CODE_PROCESSING_WITH_SHIPMENT = 'processing_with_shipment';
    const STATUS_LABEL_PROCESSING_WITH_SHIPMENT = 'Processing With Shipment';

    const STATUS_CODE_SHIPMENT_PROCESSING = 'shipment_processing';
    const STATUS_LABEL_SHIPMENT_PROCESSING = 'Shipment Processing';

    private $statuses = [
        Order::STATE_PROCESSING => [
            self::STATUS_CODE_PREPARING => self::STATUS_LABEL_PREPARING,
            self::STATUS_CODE_PROCESSING_WITH_SHIPMENT => self::STATUS_LABEL_PROCESSING_WITH_SHIPMENT,
        ],
        Order::STATE_COMPLETE => [
            self::STATUS_CODE_SHIPMENT_PROCESSING => self::STATUS_LABEL_SHIPMENT_PROCESSING
        ]
    ];

    /**#@-*/

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    private $statusResourceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StatusFactory $statusFactory
     * @param LoggerInterface $logger
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        LoggerInterface $logger,
        StatusResourceFactory $statusResourceFactory
    )
    {
        $this->logger = $logger;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Add order statuses
     *
     * @return AddDeliveryCompleteOrderStatus|void
     */
    public function apply()
    {
        foreach ($this->statuses as $state => $statuses) {
            foreach ($statuses as $code => $label) {
                $statusResource = $this->statusResourceFactory->create();
                $status = $this->statusFactory->create();
                try {
                    $status->setData([
                        'status' => $code,
                        'label' => $label
                    ]);
                    $statusResource->save($status);
                    $status->assignState($state, false, true);
                } catch (AlreadyExistsException $exception) {
                    $this->logger->error($exception->getMessage());
                } catch (\Exception $exception) {
                    $this->logger->error('Error while adding ' . $code
                        . ' status ' . $exception->getMessage());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
