define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('click', config.searchItemElement, function (e) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'select_item',
                'prdInfo': {
                    'item_list_name': config.listItemName,
                    'code': $(this).data('ap-code'),
                    'name': $(this).data('ap-name'),
                    'brand': $(this).data('ap-site-name'),
                    'cate': $(this).data('ap-cate'),
                    'apg_brand_code': $(this).data('ap-apg-code'),
                    'index': $(this).data('ap-index'),
                    'price': $(this).data('ap-final-price'),
                    'discount': $(this).data('ap-discount'),
                    'prdprice': $(this).data('ap-org-price')
                }
            });
        });
    };
});
