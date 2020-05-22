<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amore\CustomerRegistration\Api;

/**
 * POS CRUD interface.
 * @api
 * @since 100.0.2
 */
interface POSIntegrationInterface
{
    /**
     * Update a customer.
     *
     * @param \Amore\CustomerRegistration\Api\Data\CustomerInterface $customer
     * @return boolean
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update(\Amore\CustomerRegistration\Api\Data\CustomerInterface $customer);

}
