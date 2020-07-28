<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 3:53 PM
 */

namespace Eguana\GWLogistics\Api;


use Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ReverseStatusNotificationRepositoryInterface
{
    /**
     * @param int $id
     * @return ReverseStatusNotificationInterface
     */
    public function getById(int $id): ReverseStatusNotificationInterface;

    /**
     * @param ReverseStatusNotificationInterface $reverseStatusNotification
     * @return ReverseStatusNotificationInterface
     */
    public function save(ReverseStatusNotificationInterface $reverseStatusNotification): ReverseStatusNotificationInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ReverseStatusNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationSearchResultInterface;
}
