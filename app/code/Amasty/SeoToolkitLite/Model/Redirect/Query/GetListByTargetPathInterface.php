<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect\Query;

use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect\Collection;

interface GetListByTargetPathInterface
{
    /**
     * @param string $targetPath
     * @return Collection
     */
    public function execute(string $targetPath): Collection;
}
