<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 9:46 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Response;


interface HandlerInterface
{
    public function handle(array $commandSubject, array $response): void;

}