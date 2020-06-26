<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/25
 * Time: 9:36 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

abstract class EcpayCarruerType
{
    // 無載具
    const None = '';

    // 會員載具
    const Member = '1';

    // 買受人自然人憑證
    const Citizen = '2';

    // 買受人手機條碼
    const Cellphone = '3';
}
