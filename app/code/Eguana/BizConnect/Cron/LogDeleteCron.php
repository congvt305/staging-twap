<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/09/04
 * Time: 4:23 PM
 */

namespace Eguana\BizConnect\Cron;

class LogDeleteCron
{
    /**
     * @var \Eguana\BizConnect\Model\LogDeleter
     */
    private $logDeleter;

    /**
     * LogDeleteCron constructor.
     * @param \Eguana\BizConnect\Model\LogDeleter $logDeleter
     */
    public function __construct(
        \Eguana\BizConnect\Model\LogDeleter $logDeleter
    ) {
        $this->logDeleter = $logDeleter;
    }

    public function execute()
    {
        $this->logDeleter->logDeleter();
    }
}
