<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 27/10/20
 * Time: 1:03 PM
 */
namespace Eguana\LinePay\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class SubjectReader
 *
 * Subject reader readResponseObject
 */
class SubjectReader
{

    /**
     * Read Response Object
     * @param array $subject
     * @return mixed
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }
        return $response['object'];
    }
}
