define([
    'jquery'
    ], function ($) {
        'use strict';

        $.widget('satp.gotoproduct', {
            _create: function () {
                var self = this;

                self.element.find('a').click(function (e) {
                    e.preventDefault();
                    window.top.location.href = $(this).attr('href');

                    return false;
                });
            }
        });

        return $.satp.gotoproduct;
    });
