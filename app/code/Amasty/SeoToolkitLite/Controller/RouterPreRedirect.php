<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Controller;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;

class RouterPreRedirect extends RedirectRouterAbstract
{
    /**
     * @param RedirectInterface $redirect
     * @return bool
     */
    protected function isRedirectAllow(RedirectInterface $redirect): bool
    {
        return !$redirect->getUndefinedPageOnly();
    }
}
