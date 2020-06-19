<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-19
 * Time: 오후 7:28
 */

namespace Amore\Sap\Model\Consumer;

use Magento\Framework\MessageQueue\ConsumerInterface;

use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\App\ResourceConnection;

class SapInventoryStockConsumer
{
//    /**
//     * @var CallbackInvoker
//     */
//    private $invoker;
//    /**
//     * @var ResourceConnection
//     */
//    private $resource;
//    /**
//     * @var MessageController
//     */
//    private $messageController;
//    /**
//     * @var ConsumerConfigurationInterface
//     */
//    private $configuration;
//
//    /**
//     * SapInventoryStockConsumer constructor.
//     * @param CallbackInvoker $invoker
//     * @param ResourceConnection $resource
//     * @param MessageController $messageController
//     * @param ConsumerConfigurationInterface $configuration
//     */
//    public function __construct(
//        CallbackInvoker $invoker,
//        ResourceConnection $resource,
//        MessageController $messageController,
//        ConsumerConfigurationInterface $configuration
//    ) {
//        $this->invoker = $invoker;
//        $this->resource = $resource;
//        $this->messageController = $messageController;
//        $this->configuration = $configuration;
//    }

    /**
     * @param string $test
     */
    public function process($test)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/%s_test_consumer.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $logger->info($test);

        echo $test;
    }
}
