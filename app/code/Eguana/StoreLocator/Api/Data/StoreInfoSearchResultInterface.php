<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StoreInfoSearchResultInterface
 *  Eguana\StoreLocator\Api\Data
 */
interface StoreInfoSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return StoreInfoInterface[]
     */
    public function getItems();

    /**
     * @param SearchResultsInterface []
     * @return void
     */
    public function setItems(array $items);
}
