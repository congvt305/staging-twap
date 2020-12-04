<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 4:40 PM
 */
namespace Eguana\CustomerBulletin\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Note search results.
 * Interface NoteSearchResultsInterface
 */
interface NoteSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Notes list.
     *
     * @return NoteInterface[]
     */
    public function getItems();

    /**
     * Set Notes list.
     *
     * @param NoteInterface[] $items
     * @return NoteInterface
     */
    public function setItems(array $items);
}
