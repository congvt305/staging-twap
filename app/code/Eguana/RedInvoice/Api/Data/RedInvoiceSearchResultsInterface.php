<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Api\Data;

use Eguana\RedInvoice\Api\Data\RedInvoiceInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for RedInvoice search results
 * Interface RedInvoiceSearchResultsInterface
 * @api
 */
interface RedInvoiceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RedInvoice List
     *
     * @return RedInvoiceInterface[]
     */
    public function getItems();

    /**
     * Set RedInvoice List
     *
     * @param RedInvoiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
