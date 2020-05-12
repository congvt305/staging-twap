/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: false,
            loaderContext: $('.product.data.items')
        }).done(function (data) {
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).click(function (event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
        }).complete(function () {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: $('.product-reviews-wrapper').offset().top
                }, 300);
            }
        });
    }

    return function (config) {
        processReviews(config.productReviewUrl);

        $(function () {
            $('.product-info-content .reviews-actions a').click(function (event) {
                var anchor;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');

                if (anchor == "reviews") {
                    $('html, body').animate({
                        scrollTop: $('.product-reviews-wrapper').offset().top
                    }, 300);
                } else {
                    $('html, body').animate({
                        scrollTop: $('.product-reviews-wrapper #' + anchor).offset().top
                    }, 300);
                }
            });
        });
    };
});
