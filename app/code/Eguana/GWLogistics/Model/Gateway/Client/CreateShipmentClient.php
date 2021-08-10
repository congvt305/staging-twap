<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 11:20 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Client;


class CreateShipmentClient extends \Eguana\GWLogistics\Model\Gateway\Client\AbstractClient
{

    /**
     * @param array $data
     * @return array
     */
    protected function process(array $data): array
    {
        $this->_ecpayLogistics->HashKey = $data['HashKey'];
        $this->_ecpayLogistics->HashIV = $data['HashIV'];
        $this->_ecpayLogistics->Send = $data['Params'];
        $this->_ecpayLogistics->SendExtend = [
            'ReceiverStoreID' => $data['ReceiverStoreID'], //cvs store id from map request, b2c do not send
            'ReturnStoreID' => '' //cvs store id from map request, b2c do not send
        ];
        return $this->_ecpayLogistics->BGCreateShippingOrder();
    }
}
