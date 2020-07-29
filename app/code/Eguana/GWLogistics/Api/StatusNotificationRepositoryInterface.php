<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 3:53 PM
 */

namespace Eguana\GWLogistics\Api;


use Eguana\GWLogistics\Api\Data\StatusNotificationInterface;
use Eguana\GWLogistics\Api\Data\StatusNotificationSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StatusNotificationRepositoryInterface
{
    /**
     * @param int $id
     * @return StatusNotificationInterface
     */
    public function getById(int $id): StatusNotificationInterface;

    /**
     * @param StatusNotificationInterface $reverseStatusNotification
     * @return StatusNotificationInterface
     */
    public function save(StatusNotificationInterface $reverseStatusNotification): StatusNotificationInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StatusNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatusNotificationSearchResultInterface;

}
