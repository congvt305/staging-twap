define([
    'jquery',
], function ($) {
    'use strict';
    return function(Component) {
        return Component.extend({
            options: {
                targetElement: "div.block.block-minicart"
            },
            shoppingCartUrl: window.checkout.shoppingCartUrl,
            goToCart: function () {
                let self = this;
                $(self.options.targetElement).dropdownDialog('close');
                window.location.href = self.shoppingCartUrl;
            }
        });
    }
});
