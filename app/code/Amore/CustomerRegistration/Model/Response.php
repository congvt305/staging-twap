<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 29
 * Time: 오후 3:36
 */

namespace Amore\CustomerRegistration\Model;

/**
 * To set the reponse in the form of json
 * Class Response
 * @package Amore\CustomerRegistration\Model
 */
class Response implements \Amore\CustomerRegistration\Api\Data\ResponseInterface
{
    protected $code;
    protected $message;
    protected $data;

    public function setCode($code)
    {

        return  $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $code
     * @return mixed
     */
    public function setMessage($message)
    {
        return $this->message = $message;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param \Amore\CustomerRegistration\Api\Data\DataResponseInterface
     * @return mixed
     */
    public function setData($data)
    {
        return $this->data = $data;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return \Amore\CustomerRegistration\Api\Data\DataResponseInterface
     */
    public function getData()
    {
        return $this->data;
    }
}