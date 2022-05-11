<?php

namespace CJ\ReviewsImportExport\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Review\Model\ResourceModel\Rating;
use Magento\Store\Model\StoreManagerInterface;

class CreateRating implements DataPatchInterface
{
    const RATING_CODE = 'Rating';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Review\Model\Rating
     */
    private $rating;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Rating
     */
    private $ratingResource;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Review\Model\Rating $rating
     * @param \Magento\Review\Model\Rating\OptionFactory $option
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Review\Model\Rating $rating,
        \Magento\Review\Model\Rating\OptionFactory $optionFactory,
        StoreManagerInterface $storeManager,
        Rating $ratingResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->rating = $rating;
        $this->optionFactory = $optionFactory;
        $this->storeManager = $storeManager;
        $this->ratingResource = $ratingResource;
    }

    /**
     * Create rating
     *
     * @return CreateRating|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        $connection->startSetup();
        $entityId = $this->ratingResource->getEntityIdByCode('product');
        $stores = array_keys($this->storeManager->getStores(true));
        $this->rating->setRatingCode(self::RATING_CODE)
            ->setStores($stores)
            ->setIsActive(true)
            ->setEntityId($entityId)
            ->save();

        for ($i = 1; $i <= 5; $i++) {
            $option = $this->optionFactory->create();
            $option->setCode($i)
                ->setValue($i)
                ->setRatingId($this->rating->getId())
                ->setPosition($i)
                ->save();
        }
        $connection->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
