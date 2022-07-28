<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\InventoryApi\SourceRepository;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use CJ\SFLocker\Model\InitStoreTypeExtensionAttributes;

/**
 * Populate store pickup extension attributes when loading single order.
 */
class LoadStoreTypeOnGetPlugin
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
    ) {
        $this->setExtensionAttributes = $setExtensionAttributes;
    }

    /**
     * Enrich the given Source Objects with the In-Store pickup attribute
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     *
     * @return SourceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ): SourceInterface {
        $this->setExtensionAttributes->execute($source);

        return $source;
    }
}
