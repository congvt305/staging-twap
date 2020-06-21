<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/21/20
 * Time: 7:57 AM
 */

namespace Eguana\GWLogistics\Model\Lib;


abstract class EcpayScheduledPickupTime {
    const TIME_9_12 = '1';// 9~12時
    const TIME_12_17 = '2';// 12~17時
    const TIME_17_20 = '3';// 17~20時
    const UNLIMITED = '4';// 不限時
}
