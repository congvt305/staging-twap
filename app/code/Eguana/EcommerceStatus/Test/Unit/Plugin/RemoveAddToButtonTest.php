<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/15/20
 * Time: 6:08 AM
 */
namespace Eguana\EcommerceStatus\Test\Unit\Plugin\RemoveAddToButtonTest;

use Eguana\EcommerceStatus\Helper\Data;
use Eguana\EcommerceStatus\Plugin\RemoveAddToButton;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product;

/**
 * This class is used for unit testing
 *
 * Class RemoveAddToButtonTest
 * Eguana\EcommerceStatus\Test\Unit\Plugin\RemoveAddToButtonTest
 */
class RemoveAddToButtonTest extends TestCase
{

    /**
     * @var RemoveAddToButton
     */
    private $object;

    /**
     * Create Mockup
     */
    protected function setUp():void
    {
        $this->helper = $this->createMock(Data::class);
        $this->product = $this->createMock(Product::class);

        $this->object = new RemoveAddToButton(
            $this->helper
        );

        parent::setUp();
    }

    /**
     * this test case is for check class abject creating
     */
    public function testRemoveAddToButtonInstance()
    {
        $this->assertInstanceOf(RemoveAddToButton::class, $this->object);
    }

    /**
     * This test case is for plugin
     */
    public function testRemoveAddToButtonStatus()
    {
        $this->helper->expects($this->once())->method('getECommerceStatus')->will($this->returnValue(0));
        $result = $this->object->afterIsSaleable($this->product, $result = 1);
        $this->assertEquals(false, $result);
    }
}
