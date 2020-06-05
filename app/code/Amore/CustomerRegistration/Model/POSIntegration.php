<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 21
 * Time: 오후 5:37
 *
 * PHP version 7.3.18
 *
 * @category PHP_FILE
 * @package  Eguana
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 */

namespace Amore\CustomerRegistration\Model;

use Amore\CustomerRegistration\Api\Data\CustomerInterface;

/**
 * Implement the API module interface
 * Class POSIntegration
 *
 * @category PHP_FILE
 * @package  Amore\CustomerRegistration\Model
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
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