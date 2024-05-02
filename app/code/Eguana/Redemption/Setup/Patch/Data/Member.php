<?php

namespace Eguana\Redemption\Setup\Patch\Data;

use Laminas\ReCaptcha\Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Psr\Log\LoggerInterface;

class Member implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory $customerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerFactory $customerFactory,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $collectionFactory = $this->collectionFactory->create();
        $allRedemptionItems = $collectionFactory->getItems();
        foreach ($allRedemptionItems as $redemptionItem) {
            $is_Member = false;
            $customer = $this->customerFactory->create()
                ->getCollection()
                ->addAttributeToFilter([
                    ['attribute' => 'email', 'eq' => $redemptionItem->getData("email")],
                    ['attribute' => 'mobile_number', 'eq' => $redemptionItem->getData("telephone")],
                ])
                ->addAttributeToFilter('store_id', $redemptionItem->getData('store_id'))
                ->getFirstItem();
            if ($customer->getData()) {
                $is_Member = true;
            }
            try {
                $redemptionItem->setData('is_member', $is_Member)->save();
            } catch (Exception $exception) {
                $this->logger->error("Error when saving the redemption user: " . $exception->getMessage());
            }
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}

