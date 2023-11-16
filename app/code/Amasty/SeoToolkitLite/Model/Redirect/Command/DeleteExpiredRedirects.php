<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect\Command;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect\Expiration;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DeleteExpiredRedirects implements DeleteExpiredRedirectsInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Redirect
     */
    private $redirectResource;

    public function __construct(
        DateTime $dateTime,
        Redirect $redirectResource
    ) {
        $this->dateTime = $dateTime;
        $this->redirectResource = $redirectResource;
    }

    public function execute(): void
    {
        $gmtDateTime = $this->dateTime->gmtDate();
        $connection = $this->redirectResource->getConnection();

        $query = $connection->select()
            ->from(
                ['main_table' => $this->redirectResource->getMainTable()],
                [RedirectInterface::REDIRECT_ID]
            )->joinLeft(
                ['stores' => $this->redirectResource->getTable(RedirectInterface::STORE_TABLE_NAME)],
                sprintf('main_table.%1$s = stores.%1$s', RedirectInterface::REDIRECT_ID),
            )->joinLeft(
                ['expiration' => $this->redirectResource->getTable(Expiration::TABLE_NAME)],
                sprintf('main_table.%s = expiration.%s', RedirectInterface::REDIRECT_ID, Expiration::REDIRECT_ID),
            )->where(
                Expiration::EXPIRATION_DATE . ' <= ?',
                $gmtDateTime
            )->deleteFromSelect('main_table');

        $connection->query($query);
    }
}
