<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/25
 * Time: 9:56 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

abstract class EcpayVatType
{
    // 商品單價含稅價
    const Yes = '1';

    // 商品單價未稅價
    const No = '0';
}
