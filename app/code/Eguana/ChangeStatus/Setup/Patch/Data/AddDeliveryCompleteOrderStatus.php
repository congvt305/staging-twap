<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/4/21
 * Time: 10:30 AM
 */
namespace Eguana\ChangeStatus\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
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
class AddDeliveryCompleteOrderStatus implements DataPatchInterface
{
    /**#@+
     * Constants for status
     */
    const STATUS_CODE   = 'delivery_complete';
    const STATUS_LABEL  = 'Delivery Complete';
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
    ) {
        $this->logger = $logger;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Add order status "delivery_complete"
     *
     * @return AddDeliveryCompleteOrderStatus|void
     */
    public function apply()
    {
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();

        try {
            $status->setData([
                'status' => self::STATUS_CODE,
                'label' => self::STATUS_LABEL
            ]);
            $statusResource->save($status);
            $status->assignState(Order::STATE_COMPLETE, false, true);
        } catch (AlreadyExistsException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error('Error while adding delivery_complete status ' . $exception->getMessage());
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
