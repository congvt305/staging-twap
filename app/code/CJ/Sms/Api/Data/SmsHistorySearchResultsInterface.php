<?php
namespace CJ\Sms\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Eguana\EventManager\Api\Data\EventManagerInterface;

/**
 * Interface for events search results.
 * Interface EventManagerSearchResultsInterface
 */
interface SmsHistorySearchResultsInterface extends SearchResultsInterface
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
