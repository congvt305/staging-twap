<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\Operation;

use Amasty\Base\Setup\SerializedFieldDataConverter;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeDataTo135 implements UpgradeDataOperationInterface
{
    /**
     * @var SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    public function __construct(
        SerializedFieldDataConverter $fieldDataConverter,
        ProductMetadataInterface $productMetaData
    ) {
        $this->fieldDataConverter = $fieldDataConverter;
        $this->productMetaData = $productMetaData;
    }

    public function execute(ModuleDataSetupInterface $moduleDataSetup, string $setupVersion): void
    {
        if (version_compare($setupVersion, '1.3.5', '<')
            && $this->productMetaData->getVersion() >= "2.2.0"
        ) {
            $this->fieldDataConverter->convertSerializedDataToJson(
                'amasty_feed_entity',
                'entity_id',
                ['conditions_serialized']
            );
        }
    }
}
