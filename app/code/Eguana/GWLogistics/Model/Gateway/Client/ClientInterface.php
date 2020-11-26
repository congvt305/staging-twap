<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 11:21 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Client;


interface ClientInterface
{
    /**
     * @param array $request
     * @return array
     */
    public function placeRequest(array $request): array;

}