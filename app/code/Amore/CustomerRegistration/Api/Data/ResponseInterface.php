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
 * To set the response in the form of json
 * Class ResponseInterface
 * @package Amore\CustomerRegistration\Api\Data
 */
interface ResponseInterface
{

    /**
     * API code
     * LONG DESCRIPTION LINE BY LINE
     * @param string $code
     * @return mixed
     */
    public function setCode($code);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getCode();

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param string $code
     * @return mixed
     */
    public function setMessage($message);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getMessage();

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param \Amore\CustomerRegistration\Api\Data\DataResponseInterface
     * @return mixed
     */
    public function setData($data);

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return \Amore\CustomerRegistration\Api\Data\DataResponseInterface
     */
    public function getData();
}