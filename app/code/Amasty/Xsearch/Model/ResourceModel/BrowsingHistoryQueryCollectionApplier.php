<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\Xsearch\Model\ResourceModel;

use Magento\Search\Model\ResourceModel\Query\Collection;

class BrowsingHistoryQueryCollectionApplier
{
    /**
     * @var UserSearch
     */
    private $userSearch;

    public function __construct(UserSearch $userSearch)
    {
        $this->userSearch = $userSearch;
    }

    public function execute(Collection $collection, int $customerId): void
    {
        $collection->getSelect()->joinInner(
            ['user_search' => $this->userSearch->getMainTable()],
            sprintf(
                'user_search.query_id = main_table.query_id AND user_search.user_key = \'%d\'',
                $customerId
            ),
            []
        )->group('main_table.query_id')
            ->order('main_table.query_id DESC');
    }
}
