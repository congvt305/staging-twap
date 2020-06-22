<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */

namespace Eguana\StoreLocator\Api;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Eguana\StoreLocator\Api\Data\StoreInfoSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface StoreInfoRepositoryInterface
 *  Eguana\StoreLocator\Api
 */
interface StoreInfoRepositoryInterface
{
    /**
     * @param int $id
     * @return StoreInfo
     */
    public function getById($id);

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerById($customerId);

    /**
     * @param %entityName%Interface
     * @return StoreInfoInterface[]
     */
    public function save($StoreInfo);

    /**
     * @param SearchCriteriaInterface $searchcriteria
     * @return StoreInfoSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchcriteria);
}
