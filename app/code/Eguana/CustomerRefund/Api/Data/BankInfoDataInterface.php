<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 7:07 AM
 */

namespace Eguana\CustomerRefund\Api\Data;


interface BankInfoDataInterface
{
    const BANKINFO_ID = "bankinfo_id";
    const BANK_NAME = "bank_name";
    const ACCOUNT_OWNER_NAME = "account_owner_name";
    const BANK_ACCOUNT_NUMBER = "bank_account_number";
    const ORDER_ID = "order_id";

    /**
     * @return int|null
     */
    public function getBankInfoId(): ?int;

    /**
     * @param int|null $bankInfoId
     * @return void
     */
    public function setBankInfoId(?int $bankInfoId): void;

    /**
     * @return string|null
     */
    public function getBankName(): ?string;

    /**
     * @param string|null $bankName
     * @return void
     */
    public function setBankName(?string $bankName): void;

    /**
     * @return string|null
     */
    public function getAccountOwnerName(): ?string;

    /**
     * @param string|null $accountOwnerName
     * @return void
     */
    public function setAccountOwnerName(?string $accountOwnerName): void;

    /**
     * @return string|null
     */
    public function getBankAccountNumber(): ?string;

    /**
     * @param string|null $bankAccountNumber
     * @return void
     */
    public function setBankAccountNumber(?string $bankAccountNumber): void;

    /**
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * @param int|null $orderId
     */
    public function setOrderId(?int $orderId): void;

}
