<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Test\Integration\ViewModel;

use Eguana\CustomerBulletin\ViewModel\Detail;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class TestDetail
 */
class TestDetail extends TestCase
{
    /**
     * @var Detail
     */
    private $detail;

    protected function setUp()
    {
        $this->detail = Bootstrap::getObjectManager()->get(Detail::class);
    }

    /**
     * @test
     */
    public function testNotification()
    {
        $msg = "Sorry There is no Ticket with Id 12 Against Your Account";
        $notification = $this->detail->getNotification('12');
        $this->assertEquals($msg, $notification);
    }

    /**
     * @test
     */
    public function testgetTicketCloseAction()
    {
        $path = "http://local.stw.magentoshop.net/ticket/index/close/ticket_id/12";
        $ticketClosePath = $this->detail->getTicketCloseAction('12');
        $this->assertEquals($path, $ticketClosePath);
    }

    /**
     * @test
     */
    public function testAdminName()
    {
        $name = "";
        $notification = $this->detail->getAdminName('12');
        $this->assertEquals($name, $notification);
    }
}
