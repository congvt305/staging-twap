<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 1/7/20
 * Time: 7:23 PM
 */
namespace Eguana\MobileLogin\Test\Unit\Helper;

use Eguana\MobileLogin\Helper\Data;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Helper\Context;

/**
 * Class DataTest
 *
 * For the unit test of data helper
 */
class DataTest extends TestCase
{

    public function setUp(): void
    {
        $this->helper = $this->createMock(Context::class);
        $this->object = new Data($this->helper);
    }

    /**
     * testHelperInstance()
     * this will test Helper Instance
     */
    public function testHelperInstance()
    {
        $this->assertInstanceOf(Data::class, $this->object);
    }
}
