<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 6:15 PM
 */
namespace Eguana\NewsBoard\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for news search results.
 * Interface NewsSearchResultsInterface
 */
interface NewsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get News list.
     *
     * @return NewsInterface[]
     */
    public function getItems();

    /**
     * Set News list.
     *
     * @param NewsInterface[] $items
     * @return NewsInterface
     */
    public function setItems(array $items);
}
