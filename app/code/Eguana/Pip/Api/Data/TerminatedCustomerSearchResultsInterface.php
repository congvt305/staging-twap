<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 5:57 PM
 */
namespace Eguana\Pip\Api\Data;

use Eguana\Pip\Api\Data\TerminatedCustomerInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Terminated Customer search results
 * Interface TerminatedCustomerSearchResultsInterface
 * @api
 */
interface TerminatedCustomerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Terminated Customer List
     *
     * @return TerminatedCustomerInterface
     */
    public function getItems();

    /**
     * Set Terminated Customer List
     *
     * @param array $items
     * @return TerminatedCustomerSearchResultsInterface
     */
    public function setItems(array $items);
}
