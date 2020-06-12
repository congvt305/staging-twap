<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 12
 * Time: 오전 11:25
 */

namespace Amore\CustomerRegistration\Setup;

use Amore\CustomerRegistration\Model\SequenceBuilder;
use Magento\Store\Model\StoreRepository;

/**
 * In this calss we will create a sequence tables according to the existing websites
 *
 * Class AddSequenceTables
 * @package Amore\CustomerRegistration\Setup\Patch\Data
 */
class SequenceTablesCreator
{

    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;
    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @param EntityPool $entityPool
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     */
    public function __construct(
        StoreRepository $storeRepository,
        SequenceBuilder $sequenceBuilder
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Creates sales sequences.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function create()
    {
        $wesbitesId = $this->getWebsiteIds();

        foreach ($wesbitesId as $wesbiteId) {
            $this->sequenceBuilder->setStartValue(0)
                ->setWebsiteId($wesbiteId)
                ->setEntityType('customer_pos')->create();

            $this->sequenceBuilder->setStartValue(10000000)
                ->setWebsiteId($wesbiteId)
                ->setEntityType('customer_online')->create();
        }
    }

    private function getWebsiteIds()
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = [];

        foreach ($stores as $store) {
            $websiteIds[] = $store["website_id"];
        }

        return $websiteIds;
    }

}