define([
    'jquery',
    'mage/url',
    'accordion',
], function ($, url) {
    'use strict';

    var productId = $('.faqs-page').data('product-id');
    var categoryId = $('.faqs-page').data('category-id');
    var isLoadedFaq = 0;
    var stickyFaq = function () {
        var top = 0;
        var scrollTop = $(window).scrollTop();
        if ($('.faqs-page').offset()) {
            top = $('.faqs-page').height();
        }
        if (scrollTop > top && scrollTop > 0 && !isLoadedFaq) {
            isLoadedFaq = 1;
            url.setBaseUrl(window.BASE_URL);
            $.ajax({
                url: url.build('faq/index/faqajaxload'),
                type: "POST",
                showLoader: false,
                data: {product_id: productId, category_id: categoryId},
            }).done(function (result) {
                if (result['status'] === 'success') {
                    $('.lists').html(result['content']);
                    $(".list").accordion({
                        "collapsible": true,
                        "animate": {"duration": 300},
                        "active": false,
                        "multipleCollapsible": true
                    });
                }
            });
        }
    };
    stickyFaq();

    $(window).scroll(function () {
        stickyFaq();
    });
});
