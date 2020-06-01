<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Enable, adjust and copy this code for each store you run
 *
 * Store #0, default one
 *
 * if (isHttpHost("example.com")) {
 *    $_SERVER["MAGE_RUN_CODE"] = "default";
 *    $_SERVER["MAGE_RUN_TYPE"] = "store";
 * }
 *
 * @param string $host
 * @return bool
 */
function isHttpHost(string $host)
{
    if (!isset($_SERVER['HTTP_HOST'])) {
        return false;
    }
    return $_SERVER['HTTP_HOST'] === $host;
}

function getStoreCodeFromHost($serverHost)
{
    $storeCode = explode('.', $serverHost);
    if (isset($storeCode[0]) && $storeCode[0]) {
        return $storeCode[0];
    }
    return false;
}

$storeCodes = ['default', 'tw_laneige'];

if (isHttpHost("tw.sulwhasoo.com")
    || isHttpHost("mcprod.tw.sulwhasoo.com")
    || isHttpHost("tw.laneige.com.c.kqevkj6gpg7si.ent.magento.cloud")
    || isHttpHost("mcstaging.tw.laneige.com.c.kqevkj6gpg7si.dev.ent.magento.cloud")
    || isHttpHost("integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.tw.sulwhasoo.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "default";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("tw.laneige.com")
    || isHttpHost("mcprod.tw.laneige.com")
    || isHttpHost("l.tw.laneige.com.c.kqevkj6gpg7si.ent.magento.cloud")
    || isHttpHost("l.mcstaging.tw.laneige.com.c.kqevkj6gpg7si.dev.ent.magento.cloud")
    || isHttpHost("l.integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.tw.laneige.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "tw_laneige";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
}
