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
     * Update a customer.
     *
     * @param CustomerInterface $customer customer data interface
     *
     * @return boolean
     * @throws InputException If bad input is provided
     * @throws InputMismatchException If the provided email is already used
     * @throws LocalizedException if localized exception occurs
     */
    public function update(CustomerInterface $customer);
}
