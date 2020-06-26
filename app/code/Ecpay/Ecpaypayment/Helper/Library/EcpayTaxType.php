<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/25
 * Time: 9:55 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

abstract class EcpayTaxType
{
    // 應稅
    const Dutiable = '1';

    // 零稅率
    const Zero = '2';

    // 免稅
    const Free = '3';

    // 應稅與免稅混合(限收銀機發票無法分辦時使用，且需通過申請核可)
    const Mix = '9';
}
