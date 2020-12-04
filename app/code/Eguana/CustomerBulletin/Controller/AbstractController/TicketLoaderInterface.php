<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
namespace Eguana\CustomerBulletin\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Interface \Eguana\CustomerBulletin\Controller\AbstractController\TicketLoaderInterface
 *
 */
interface TicketLoaderInterface
{
    /**
     * Load Ticket
     *
     * @param RequestInterface $request
     * @return bool|ResultInterface
     */
    public function load(RequestInterface $request);
}
