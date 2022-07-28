<?php

namespace CJ\VLogicOrder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Psr\Log\LoggerInterface;

class AddOrderStatusProcessing implements DataPatchInterface
{
    /**#@+
     * Constants for status
     */

    const STATUS_CODE_PROCESSING = 'processing';
    const STATUS_LABEL_PROCESSING = 'Processing';

    private $statuses = [
        Order::STATE_PROCESSING => [
            self::STATUS_CODE_PROCESSING => self::STATUS_LABEL_PROCESSING,
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
     * @return AddOrderStatusProcessing|void
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
