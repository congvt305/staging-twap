<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\InventoryApi\SourceRepository;

use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use CJ\SFLocker\Model\InitStoreTypeExtensionAttributes;

/**
 * Populate store pickup extension attribute when loading a list of orders.
 */
class LoadStoreTypeOnGetListPlugin
{
    /**
     * @var InitStoreTypeExtensionAttributes
     */
    private $setExtensionAttributes;

    /**
     * @param InitStoreTypeExtensionAttributes $setExtensionAttributes
     */
    public function __construct(
        InitStoreTypeExtensionAttributes $setExtensionAttributes
    )
    {
        $this->setExtensionAttributes = $setExtensionAttributes;
    }

    /**
     * Enrich the given Source Objects with the In-Store pickup attribute
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceSearchResultsInterface $sourceSearchResults
     *
     * @return SourceSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        SourceRepositoryInterface    $subject,
        SourceSearchResultsInterface $sourceSearchResults
    ): SourceSearchResultsInterface
    {
        $items = $sourceSearchResults->getItems();
        array_walk(
            $items,
            [$this->setExtensionAttributes, 'execute']
        );

        return $sourceSearchResults;
    }
}
