define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/wysiwyg',
    'wysiwygAdapter',
], function ($,uiRegistry, wysiwyg, wysiwygAdapter) {
    'use strict';

    return wysiwyg.extend({
        onUpdate: function (value) {
            try {
                if (value != wysiwygAdapter.getContent()) {
                    wysiwygAdapter.setContent(value);
                }
            } catch (e) {}
            return this._super();
        },
    });
});