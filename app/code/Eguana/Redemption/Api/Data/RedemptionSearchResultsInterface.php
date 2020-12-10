<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 3:37 PM
 */
namespace Eguana\Redemption\Api\Data;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for redemption search results
 * Interface RedemptionSearchResultsInterface
 * @api
 */
interface RedemptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Redemption List
     *
     * @return RedemptionInterface[]
     */
    public function getItems();

    /**
     * Set Redemption List
     *
     * @param RedemptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
