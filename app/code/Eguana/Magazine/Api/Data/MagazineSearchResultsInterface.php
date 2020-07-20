<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 7/16/20
 * Time: 12:51 AM
 */
namespace Eguana\Magazine\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface MagazineSearchResultsInterface
 */
interface MagazineSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return MagazineInterface[]
     */
    public function getItems();

    /**
     * @param FaqInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
