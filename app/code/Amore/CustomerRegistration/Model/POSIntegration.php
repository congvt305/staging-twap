<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 21
 * Time: 오후 5:37
 */

namespace Amore\CustomerRegistration\Model;

use Amore\CustomerRegistration\Api\Data\CustomerInterface;

/**
 * Implement the API module interface
 * Class POSIntegration
 */
class POSIntegration implements \Amore\CustomerRegistration\Api\POSIntegrationInterface
{

    /**
     * Implement the API module interface
     *
     * @param CustomerInterface $customer Customer
     *
     * @return bool|void
     */
    public function update(CustomerInterface $customer)
    {
        // TODO: Implement update() method.
    }
}
