<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/16/20, 2:17 AM
 *
 */

namespace Eguana\BizConnect\Model\LoggedOperation;

class Test
{
    /**
     * @var string
     */
    private $test;

    /**
     * @var string
     */
    private $testName;

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->getData('test');
    }

    /**
     * @param string $test
     *
     * @return Test
     */
    public function setTest(string $test): Test
    {
        $this->setData('test', $test);
        return $this;
    }




}
