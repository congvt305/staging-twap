<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\ResourceModel\Redirect;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Expiration
{
    public const TABLE_NAME = 'amasty_seotoolkit_redirect_expiration';
    public const EXPIRATION_DATE = 'expiration_date';
    public const REDIRECT_ID = 'redirect_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    /**
     * @param int $redirectId
     * @param string $expirationDate
     * @return void
     */
    public function saveExpirationData(int $redirectId, string $expirationDate): void
    {
        $data = [
            self::REDIRECT_ID => $redirectId,
            self::EXPIRATION_DATE => $expirationDate
        ];
        
        $this->getConnection()->insert($this->getTableName(Expiration::TABLE_NAME), $data);
    }
}
