define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/knockout/template/loader',
    'mage/template'
], function (ko, $, templateLoader, template) {
    'use strict';

    var blockLoaderTemplatePath = 'ui/block-loader',
        blockContentLoadingClass = '_block-content-loading',
        blockLoader,
        blockLoaderClass,
        blockLoaderElement = $.Deferred(),
        loaderImageHref = $.Deferred();

    templateLoader.loadTemplate(blockLoaderTemplatePath).done(function (blockLoaderTemplate) {
        loaderImageHref.done(function (loaderHref) {
            blockLoader = template($.trim(blockLoaderTemplate), {
                loaderImageHref: loaderHref
            });
            blockLoader = $(blockLoader);
            blockLoaderClass = '.' + blockLoader.attr('class');
            blockLoaderElement.resolve();
        });
    });

    /**
     * Helper function to check if blockContentLoading class should be applied.
     * @param {Object} element
     * @returns {Boolean}
     */
    function isLoadingClassRequired(element) {
        var position = element.css('position');

        if (position === 'absolute' || position === 'fixed') {
            return false;
        }

        return true;
    }

    /**
     * Add loader to block.
     * @param {Object} element
     */
    function addBlockLoader(element) {
        element.find(':focus').trigger('blur');
        element.find('input:disabled, select:disabled').addClass('_disabled');
        // CUSTOM HERE
        element.find('input:not("#discount-code, #bc-code, #ff-phonenumber"), select').prop('disabled', true);
        // END CUSTOM
        if (isLoadingClassRequired(element)) {
            element.addClass(blockContentLoadingClass);
        }
        element.append(blockLoader.clone());
    }

    /**
     * Remove loader from block.
     * @param {Object} element
     */
    function removeBlockLoader(element) {
        if (!element.has(blockLoaderClass).length) {
            return;
        }
        element.find(blockLoaderClass).remove();
        // CUSTOM HERE
        element.find('input:not("._disabled,#discount-code, #bc-code, #ff-phonenumber"), select:not("._disabled")').prop('disabled', false);
        // END CUSTOM
        element.find('input:disabled, select:disabled').removeClass('_disabled');
        element.removeClass(blockContentLoadingClass);
    }

    return function (loaderHref) {
        loaderImageHref.resolve(loaderHref);
        ko.bindingHandlers.blockLoader = {
            /**
             * Process loader for block
             * @param {String} element
             * @param {Boolean} displayBlockLoader
             */
            update: function (element, displayBlockLoader) {
                element = $(element);

                if (ko.unwrap(displayBlockLoader())) {
                    blockLoaderElement.done(addBlockLoader(element));
                } else {
                    blockLoaderElement.done(removeBlockLoader(element));
                }
            }
        };
    };
});
