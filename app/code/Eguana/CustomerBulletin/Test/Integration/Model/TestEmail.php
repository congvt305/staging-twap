<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Test\Integration\Model;

use Eguana\CustomerBulletin\Model\Email\EmailSender;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class TestEmail
 */
class TestEmail extends TestCase
{
    /**
     * @var EmailSender
     */
    private $email;

    protected function setUp()
    {
        $this->email = Bootstrap::getObjectManager()->get(EmailSender::class);
    }

    /**
     * @test
     */
    public function testgetCustomerName()
    {
        $expectedName = 'test';
        $name = $this->email->getCustomerName(1034);
        $this->assertEquals($expectedName, $name);
    }

    /**
     * @test
     */
    public function testgetCustomerEmail()
    {
        $expectedEmail= 'bilalyounas1543@gmail.com';
        $email = $this->email->getCustomerEmail(1034);
        $this->assertEquals($expectedEmail, $email);
    }

}
