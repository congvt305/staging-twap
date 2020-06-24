<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface FaqSearchResultsInterface
 */
interface FaqSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return FaqInterface[]
     */
    public function getItems();

    /**
     * @param FaqInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
