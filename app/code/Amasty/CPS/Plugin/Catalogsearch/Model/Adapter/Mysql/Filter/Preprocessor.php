<?php

namespace Amasty\CPS\Plugin\Catalogsearch\Model\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\App\ResourceConnection;

class Preprocessor
{
    public const BRAND_FILTER_NAME = 'ambrand_id_filter';
    public const BRAND_FILTER_FIELD = 'ambrand_id';
    public const FILTER_SUFFIX = '_filter';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundProcess(
        $subject,
        callable $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ) {
        if ($filter->getName() === self::BRAND_FILTER_NAME) {
            $newFieldName = $filter->getField() . self::FILTER_SUFFIX . '.' . self::BRAND_FILTER_FIELD;
            return str_replace(
                $this->connection->quoteIdentifier($filter->getField()),
                $this->connection->quoteIdentifier($newFieldName),
                $query
            );
        }

        return $proceed($filter, $isNegation, $query);
    }
}
