<?php
declare(strict_types=1);

namespace CJ\SFLocker\Model;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Set store-pickup related source extension attributes
 */
class InitStoreTypeExtensionAttributes
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * Set store-pickup related source extension attributes.
     *
     * @param SourceInterface $source
     */
    public function execute(SourceInterface $source): void
    {
        if (!$source instanceof DataObject) {
            return;
        }
        $storeType = $source->getData('store_type');
        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceInterface::class);
            /** @noinspection PhpParamsInspection */
            $source->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes
            ->setStoreType($storeType);
    }
}
