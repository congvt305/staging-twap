<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 29
 * Time: 오후 3:32
 */

namespace Amore\CustomerRegistration\Api\Data;


/**
 * To set the data response
 * Class ResponseInterface
 * @package Amore\CustomerRegistration\Api\Data
 */
interface DataResponseInterface
{

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setStatusCode($statusCode);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getStatusCode();

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setStatusMessage($statusMessage);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getStatusMessage();

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $statusCode
     * @return mixed
     */
    public function setCstmIntgSeq($cstmIntgSeq);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getCstmIntgSeq();

}