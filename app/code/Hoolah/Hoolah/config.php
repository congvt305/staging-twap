<?php

    // Following settings shall never be changed unless instructed by hoolah

    define('HOOLAH_DISPLAY_OPERATION_MODE',     true);
    define('HOOLAH_SUPPORTED_COUNTRIES',        'SG,MY,HK,TH');

    define('HOOLAH_API_HOST_PROD',              'https://prod-merchant-service.hoolah.co');
    define('HOOLAH_API_HOST_SANDBOX',           'https://demo-merchant-service.demo-hoolah.co');

    define('HOOLAH_CHECKOUT_URL_PROD_SG',       'https://js.secure-hoolah.co/?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');
    define('HOOLAH_CHECKOUT_URL_SANDBOX_SG',    'https://demo-checkout.shopback.com/paylater?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');

    define('HOOLAH_CHECKOUT_URL_PROD_MY',       'https://my.js.secure-hoolah.co/?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');
    define('HOOLAH_CHECKOUT_URL_SANDBOX_MY',    'https://demo-checkout.shopback.com/paylater?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');

    define('HOOLAH_CHECKOUT_URL_PROD_TH',       'https://th.js.secure-hoolah.co/?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');
    define('HOOLAH_CHECKOUT_URL_SANDBOX_TH',    'https://demo-checkout.shopback.com/paylater?ORDER_CONTEXT_TOKEN=%s&platform=magento&version=%s');

    define('HOOLAH_WIDGET_URL_CUSTOM',          'https://merchant.cdn.hoolah.co/%s/hoolah-library.js');
    define('HOOLAH_WIDGET_URL_GENERAL',         'https://cdn.hoolah.co/integration/hoolah-library/hoolah-library-general.css');

    define('HOOLAH_EXPLAINER_PREVIEW',          'https://merchant.cdn.hoolah.co/%s/hoolah-explainer.html#f242-492f-95ca-7f8537500f33');
    define('HOOLAH_EXPLAINER_CSS',              'https://cdn.hoolah.co/integration/hoolah-explainer/hoolah-explainer.css');

    define('HOOLAH_EXT_SETTINGS',               'https://merchant.cdn.hoolah.co/%s/Magento-settings.json');
    define('HOOLAH_EXT_SETTINGS_CHECKUP_INTERVAL', 1);

    define('HOOLAH_LOG_DEMO',                   'https://log-reporting.hoolah.co/demo/%s?relatesTo=%s');
    define('HOOLAH_LOG_PROD',                   'https://log-reporting.hoolah.co/prod/%s?relatesTo=%s');
