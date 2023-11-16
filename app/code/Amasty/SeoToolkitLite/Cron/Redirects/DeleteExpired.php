<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Cron\Redirects;

use Amasty\SeoToolkitLite\Model\Redirect\Command\DeleteExpiredRedirectsInterface;

class DeleteExpired
{
    /**
     * @var DeleteExpiredRedirectsInterface
     */
    private $deleteExpiredRedirects;

    public function __construct(
        DeleteExpiredRedirectsInterface $deleteExpiredRedirects
    ) {
        $this->deleteExpiredRedirects = $deleteExpiredRedirects;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->deleteExpiredRedirects->execute();
    }
}
