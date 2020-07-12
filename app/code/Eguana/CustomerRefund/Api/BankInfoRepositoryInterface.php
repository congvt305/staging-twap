<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 8:57 AM
 */

namespace Eguana\CustomerRefund\Api;


use Eguana\CustomerRefund\Api\Data\BankInfoDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface BankInfoRepositoryInterface
{
    /**
     * @param int $bankInfoId
     * @return BankInfoDataInterface
     */
    public function getById(int $bankInfoId): BankInfoDataInterface;

    /**
     * @param BankInfoDataInterface $bankInfoData
     * @return BankInfoDataInterface
     */
    public function save(BankInfoDataInterface $bankInfoData): BankInfoDataInterface;

    /**
     * @param BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function delete(BankInfoDataInterface $bankInfoData): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $orderId
     * @return BankInfoDataInterface
     */
    public function getByOrderId(int $orderId): BankInfoDataInterface;

}
