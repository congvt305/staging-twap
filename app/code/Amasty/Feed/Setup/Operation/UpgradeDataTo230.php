<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Model\Import;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeDataTo230 implements UpgradeDataOperationInterface
{
    /**
     * @var Import
     */
    private $import;

    public function __construct(
        Import $import
    ) {
        $this->import = $import;
    }

    public function execute(ModuleDataSetupInterface $moduleDataSetup, string $setupVersion): void
    {
        if (version_compare($setupVersion, '2.3.0', '<')) {
            $this->import->update('google');
        }
    }
}
