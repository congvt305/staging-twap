<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 8
 * Time: 오후 1:17
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Amore\CustomerRegistration\Helper\Data;

/**
 * In this class we will call the POS API
 * Class POSSystem
 * @package Amore\CustomerRegistration\Model
 */
class POSSystem
{

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var Data
     */
    private $confg;

    public function __construct(ZendClientFactory $httpClientFactory,
                                Data $confg,
                                DateTime $date)
    {
        $this->httpClientFactory = $httpClientFactory;
        $this->date = $date;
        $this->confg = $confg;
    }

    public function getMemberInfo($firstName, $lastName, $mobileNumber)
    {
        $posData = $this->callPOSInfoAPI($firstName, $lastName, $mobileNumber);
        $posData['birthDay'] =  substr_replace($posData['birthDay'], '/', 4, 0);
        $posData['birthDay'] =  substr_replace($posData['birthDay'], '/', 7, 0);
        return $posData;
    }

    private function callPOSInfoAPI($firstName, $lastName, $mobileNumber)
    {
        $result = [];

        $result = [
            'cstmIntgSeq' => 'TW10210000001',
            'cstmNO'=>'TW1020000012345',
            'cstmSeq'=>'tw10119130',
            'firstName'=>'學榮',
            'lastName'=>'金',
            'birthDay'=>'20000101',
            'mobileNo'=>'0912345678',
            'email'=>'xxxx@gmail.com',
            'sex'=>'F',
            'emailYN'=>'Y',
            'smsYN'=>'Y',
            'callYN'=>'N',
            'dmYN'=>'N',
            'homeCity'=>'T001',
            'homeState'=>'100',
            'homeAddr1'=>'1-1',
            'homeZip'=>'406'
         ];
        return $result;

        /** @var ZendClient $client */
        /*$client = $this->httpClientFactory->create();
        $client->setUri($this->confg->getMemberJoinURL());
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept','application/json');
        $client->setHeaders("Authorization","Bearer yourvalue");
        $client->setParameterPost([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'mobileNumber' => $mobileNumber,
        ]);

        try {
            $response = $client->request();

            $responseArray = [];
            parse_str(strstr($response->getBody(), 'RESULT'), $responseArray);

            $result->setData(array_change_key_case($responseArray, CASE_LOWER));
            $result->setData('result_code', $result->getData('result'));
        } catch (\Zend_Http_Client_Exception $e) {
            return $e->getMessage();
        }

        return $result;*/
    }

    public function callMemberJoin($customer, $action)
    {

    }
}
