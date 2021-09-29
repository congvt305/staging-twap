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

//function getStoreCodeFromHost($serverHost)
//{
//    $storeCode = explode('.', $serverHost);
//    if (isset($storeCode[0]) && $storeCode[0]) {
//        return $storeCode[0];
//    }
//    return false;
//}
//function isAdminBackendUri($uri)
//{
//    return (strpos($uri, '/admin') !== false);
//}

$storeCodes = ['default', 'tw_laneige'];

if (isHttpHost("tw.sulwhasoo.com")
    || isHttpHost("mcprod.tw.sulwhasoo.com")
    || isHttpHost("s.integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("s.dev-54ta5gq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.tw.sulwhasoo.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "default";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("tw.laneige.com")
    || isHttpHost("mcprod.tw.laneige.com")
    || isHttpHost("l.integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("l.dev-54ta5gq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.tw.laneige.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "tw_laneige";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
} elseif (isHttpHost("laneige.com.vn")
    || isHttpHost("vn.laneige.com")
    || isHttpHost("www.laneige.com.vn")
    || isHttpHost("mcprod.vn.laneige.com")
    || isHttpHost("vl.integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("vl.dev-54ta5gq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.vn.laneige.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "vn_laneige";
    $_SERVER["MAGE_RUN_TYPE"] = "store";

} elseif (isHttpHost("gapm-bo1.amorepacific.com")
    || isHttpHost("mcprod.gapm-bo1.amorepacific.com")
    || isHttpHost("integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("dev-54ta5gq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("mcstaging.gapm-bo1.amorepacific.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "admin";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
} elseif (isHttpHost("sulwhasoo.com.vn")
    || isHttpHost("mcprod.sulwhasoo.com.vn")
    || isHttpHost("mcstaging.sulwhasoo.com.vn")
    || isHttpHost("vs.integration-5ojmyuq-kqevkj6gpg7si.ap-3.magentosite.cloud")
    || isHttpHost("vs.dev-54ta5gq-kqevkj6gpg7si.ap-3.magentosite.cloud")) {
    $_SERVER["MAGE_RUN_CODE"] = "vn_sulwhasoo";
    $_SERVER["MAGE_RUN_TYPE"] = "store";
}
//if (isAdminBackendUri($_SERVER['REQUEST_URI'])) {
//    $_SERVER["MAGE_RUN_CODE"] = "default";
//    $_SERVER["MAGE_RUN_TYPE"] = "store";
//}
