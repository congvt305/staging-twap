<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 6:15 PM
 */
namespace Eguana\EventManager\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Eguana\EventManager\Api\Data\EventManagerInterface;

/**
 * Interface for events search results.
 * Interface EventManagerSearchResultsInterface
 */
interface EventManagerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return EventManagerInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param EventManagerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
