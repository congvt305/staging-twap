<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/09/21
 * Time: 10:26 AM
 */

namespace Ecpay\Ecpaypayment\Helper\Library;

// 通知類別
abstract class EcpayAllowanceNotifyType
{
    // 簡訊通知
    const Sms = 'S';

    // 電子郵件通知
    const Email = 'E';

    // 皆通知
    const All = 'A';

    // 皆不通知
    const None = 'N';
}
