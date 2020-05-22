<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 21
 * Time: 오후 12:49
 */

namespace Amore\CustomerRegistration\Test\Unit\ViewModel;

use Amore\CustomerRegistration\ViewModel\POS;
use PHPUnit\Framework\TestCase;
use Amore\CustomerRegistration\Helper\Data;

class POSTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $helper;

    /**
     * @var POS
     */
    private $object;



    protected function setUp() : void
    {
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new POS(
            $this->helper
        );

        parent::setUp();
    }

    /**
     * testViewModelInstance()
     * this will test viewmodel Instance
     */
    public function testViewModelInstance()
    {
        $this->assertInstanceOf(POS::class, $this->object);
    }

    public function testGetTermsCmsBlockId()
    {
       $this->assertNotEmpty($this->object->getTermsCmsBlockId());
    }
}
