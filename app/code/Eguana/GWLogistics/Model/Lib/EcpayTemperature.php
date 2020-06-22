<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/21/20
 * Time: 7:56 AM
 */

namespace Eguana\GWLogistics\Model\Lib;


abstract class EcpayTemperature {
    const ROOM = '0001';// 常溫
    const REFRIGERATION = '0002';// 冷藏
    const FREEZE = '0003';// 冷凍
}
