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
 * To set the data response in the json format of response
 * Class Response
 * @package Amore\CustomerRegistration\Model
 */
class DataResponse implements \Amore\CustomerRegistration\Api\Data\DataResponseInterface
{
    protected $statusCode;
    protected $statusMessage;
    protected $cstmIntgSeq;

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setStatusCode($statusCode)
    {
        return $this->statusCode = $statusCode;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setStatusMessage($statusMessage)
    {
        return $this->statusMessage = $statusMessage;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setCstmIntgSeq($cstmIntgSeq)
    {
        return $this->cstmIntgSeq = $cstmIntgSeq;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getCstmIntgSeq()
    {
        return $this->cstmIntgSeq;
    }
}