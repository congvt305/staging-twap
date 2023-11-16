<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect\Query;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect\Collection;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect\CollectionFactory;

class GetListByTargetPath implements GetListByTargetPathInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(string $targetPath): Collection
    {
        $collection =  $this->collectionFactory->create()
            ->addFieldToFilter(RedirectInterface::TARGET_PATH, $targetPath);
        $collection->getSelect()->joinLeft(
            ['stores' => $collection->getTable(RedirectInterface::STORE_TABLE_NAME)],
            sprintf('main_table.%1$s = stores.%1$s', RedirectInterface::REDIRECT_ID),
            sprintf('GROUP_CONCAT(stores.%s) as store_ids', RedirectInterface::STORE_ID)
        )->group(sprintf('main_table.%s', RedirectInterface::REDIRECT_ID));
        
        return $collection;
    }
}
