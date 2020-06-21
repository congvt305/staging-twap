<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/21/20
 * Time: 7:57 AM
 */

namespace Eguana\GWLogistics\Model\Lib;


abstract class EcpayDistance {
    const SAME = '00';// 同縣市
    const OTHER = '01';// 外縣市
    const ISLAND = '02';// 離島
}
