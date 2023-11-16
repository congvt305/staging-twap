<?php

declare(strict_types=1);

namespace Amasty\CPS\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var InstallSchema\UpdateBrandColumn
     */
    private $updateBrandColumn;

    public function __construct(InstallSchema\UpdateBrandColumn $updateBrandColumn)
    {
        $this->updateBrandColumn = $updateBrandColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->updateBrandColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
