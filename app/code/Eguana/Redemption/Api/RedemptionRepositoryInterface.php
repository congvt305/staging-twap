<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 1:47 PM
 */
namespace Eguana\Redemption\Api;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface RedemptionRepositoryInterface
 * @api
 */
interface RedemptionRepositoryInterface
{
    /**
     * Save Redemption
     *
     * @param $redemption
     * @return RedemptionInterface
     */
    public function save($redemption);

    /**
     * Retrieve Redemption
     *
     * @param int $redemptionId
     * @return RedemptionInterface
     */
    public function getById($redemptionId);

    /**
     * Retrieve redemption matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Redemption
     *
     * @param $redemption
     * @return bool true on success
     */
    public function delete($redemption);

    /**
     * Delete Redemption by Id
     *
     * @param int $redemptionId
     * @return bool true on success
     */
    public function deleteById($redemptionId);
}
