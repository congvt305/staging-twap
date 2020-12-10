<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/10/20
 * Time: 11:40 PM
 */
namespace Eguana\Redemption\Api\Data;

use Eguana\Redemption\Api\Data\CounterInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for counter search results
 * Interface CounterSearchResultsInterface
 * @api
 */
interface CounterSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Counter List
     *
     * @return CounterInterface[]
     */
    public function getItems();

    /**
     * Set Counter List
     *
     * @param CounterInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
