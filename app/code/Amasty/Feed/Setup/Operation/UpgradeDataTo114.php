<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\Operation;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeDataTo114 implements UpgradeDataOperationInterface
{
    /**
     * @var Repository
     */
    private $attributeRepository;

    public function __construct(Repository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function execute(ModuleDataSetupInterface $moduleDataSetup, string $setupVersion): void
    {
        if (version_compare($setupVersion, '2.3.1', '<')) {
            $attributesForConditions = ['status', 'quantity_and_stock_status'];
            foreach ($attributesForConditions as $code) {
                $attribute = $this->attributeRepository->get($code);
                $attribute->setIsUsedForPromoRules(true);
                $this->attributeRepository->save($attribute);
            }
        }
    }
}
