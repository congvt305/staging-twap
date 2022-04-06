<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\ModuleDataSetupInterface;

interface UpgradeDataOperationInterface
{
    public function execute(ModuleDataSetupInterface $moduleDataSetup, string $setupVersion): void;
}
