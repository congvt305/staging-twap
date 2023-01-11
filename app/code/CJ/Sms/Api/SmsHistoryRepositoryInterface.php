<?php
declare(strict_types=1);

namespace CJ\Sms\Api;

use CJ\Sms\Api\Data\SmsHistoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared inter
 * interface SmsHistoryRepositoryInterface
 */
interface SmsHistoryRepositoryInterface
{
    /**
     * Save event.
     *
     * @param SmsHistoryInterface $smsHistory
     * @return mixed
     */
    public function save(SmsHistoryInterface $smsHistory);

    /**
     * Retrieve Event.
     *
     * @param $entityId
     * @return mixed
     */
    public function getById($entityId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event.
     *
     * @param SmsHistoryInterface $smsHistory
     * @return mixed
     */
    public function delete(SmsHistoryInterface $smsHistory);

    /**
     * Delete event by ID.
     *
     * @param $entityId
     * @return mixed
     */
    public function deleteById($entityId);
}
