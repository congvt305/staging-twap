<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect\Command;

interface DeleteExpiredRedirectsInterface
{
    /**
     * @return void
     */
    public function execute(): void;
}
