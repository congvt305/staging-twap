<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 *
 */

namespace Amore\CustomerRegistration\Api;

use Amore\CustomerRegistration\Api\Data\CustomerInterface;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * POS CRUD interface.
 *
 * @api
 * @since    100.0.2
 */
interface POSIntegrationInterface
{
    /**
     * To Update a customer.
     *
     * @param string $cstmIntgSeq
     * @param string $firstName
     * @param string $lastName
     * @param string $birthDay
     * @param string $mobileNo
     * @param string $email
     * @param string $sex
     * @param string $emailYN
     * @param string $smsYN
     * @param string $callYN
     * @param string $dmYN
     * @param string $homeCity
     * @param string $homeState
     * @param string $homeAddr1
     * @param string $homeZip
     * @param string $statusCD
     * @param string $salOrgCd
     * @param string $salOffCd
     * @return \Amore\CustomerRegistration\Api\Data\ResponseInterface
     * @throws InputException If bad input is provided
     * @throws InputMismatchException If the provided email is already used
     * @throws LocalizedException if localized exception occurs
     */
    public function update(
        $cstmIntgSeq,
        $firstName,
        $lastName,
        $birthDay,
        $mobileNo,
        $email,
        $sex,
        $emailYN,
        $smsYN,
        $callYN,
        $dmYN,
        $homeCity,
        $homeState,
        $homeAddr1,
        $homeZip,
        $statusCD,
        $salOrgCd,
        $salOffCd
    );
}
