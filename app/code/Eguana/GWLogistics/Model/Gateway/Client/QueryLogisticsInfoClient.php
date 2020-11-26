<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/12/20
 * Time: 9:02 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Client;

class QueryLogisticsInfoClient extends \Eguana\GWLogistics\Model\Gateway\Client\AbstractClient
{
    public function process(array $data): array
    {
        $this->_ecpayLogistics->HashKey = $data['HashKey'];
        $this->_ecpayLogistics->HashIV = $data['HashIV'];
        $this->_ecpayLogistics->Send = $data['Params'];

        return $this->_ecpayLogistics->QueryLogisticsInfo();
    }
}
