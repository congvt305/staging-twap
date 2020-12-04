<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 27/10/20
 * Time: 12:25 PM
 */
namespace Eguana\LinePay\Gateway\Validator;

/**
 * Class ErrorCodeProvider
 *
 * Retrieves list of error codes from Linepay response
 */
class ErrorCodeProvider
{

    /**
     * Retrieves list of error codes from Linepay response.
     * @param $response
     * @return array
     */
    public function getErrorCodes($response): array
    {
        $result = [];
        if (isset($response['returnCode'])) {
            $result[] = $response['returnCode'];
        }
         return $result;
    }
}
