<?php

declare(strict_types=1);

namespace Amasty\CPS\Setup\InstallSchema;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpdateBrandColumn
{
    public const BRAND_ID = 'brand_id';

    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(BrandProductInterface::MAIN_TABLE);
        if ($setup->getConnection()->tableColumnExists($table, self::BRAND_ID)) {
            $setup->getConnection()->changeColumn(
                $table,
                self::BRAND_ID,
                BrandProductInterface::AMBRAND_ID,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Brand Link ID'
                ]
            );
        }
    }
}
