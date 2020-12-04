<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 06:00 PM
 */
namespace Eguana\Redemption\Model\ResourceModel\Redemption\Relation\Store;

use Eguana\Redemption\Model\ResourceModel\Redemption;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used to read stores handle
 *
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Redemption
     */
    private $resourceRedemption;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReadHandler constructor.
     *
     * @param Redemption $resourceRedemption
     * @param LoggerInterface $logger
     */
    public function __construct(
        Redemption $resourceRedemption,
        LoggerInterface $logger
    ) {
        $this->resourceRedemption = $resourceRedemption;
        $this->logger = $logger;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        try {
            if ($entity->getId()) {
                $stores = $this->resourceRedemption->lookupStoreIds((int)$entity->getId());
                $counters = $this->resourceRedemption->lookupCounterIds((int)$entity->getId());
                $entity->setData('store_id', $stores);
                $entity->setData('stores', $stores);
                $entity->setData('offline_store_id', $counters);
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $entity;
    }
}
