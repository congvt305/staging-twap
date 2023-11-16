<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\Xsearch\Model\Indexer;

class ElasticSearchStockStatusStructureMapper
{
    public const STOCK_STATUS = 'stock_status';
    public const TYPE_INTEGER = 'integer';

    /**
     * @return array
     */
    public function buildEntityFields(): array
    {
        return [self::STOCK_STATUS => ['type' => self::TYPE_INTEGER]];
    }
}
