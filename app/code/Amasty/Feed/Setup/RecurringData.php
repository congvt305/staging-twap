<?php
declare(strict_types=1);

namespace Amasty\Feed\Setup;

use Amasty\Feed\Setup\Operation\UpgradeDataOperationInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    /**
     * @var array
     */
    private $operations;

    public function __construct(
        array $operations = []
    ) {
        $this->operations = $operations;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->upgradeDataTo270($setup, $context);
    }

    private function upgradeDataTo270(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        foreach ($this->operations as $operation) {
            if ($operation instanceof UpgradeDataOperationInterface) {
                $operation->execute($setup, (string)$context->getVersion());
            }
        }
        $setup->endSetup();
    }
}
