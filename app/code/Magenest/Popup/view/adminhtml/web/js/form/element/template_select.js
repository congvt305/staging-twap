define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'wysiwygAdapter',
], function ($,uiRegistry, select, wysiwygAdapter) {
    'use strict';

    const reloadPageBuilder = function (pageBuilder) {
        pageBuilder.isComponentInitialized(false);

        let events = require('Magento_PageBuilder/js/events');
        let PageBuilder = require('Magento_PageBuilder/js/page-builder');

        pageBuilder.loading(true);
        pageBuilder.pageBuilder = new PageBuilder(
            pageBuilder.wysiwygConfigData(),
            pageBuilder.value()
        );
        events.trigger('pagebuilder:register', {
            ns: pageBuilder.ns,
            instance: pageBuilder.pageBuilder
        });
        pageBuilder.initPageBuilderListeners();
        pageBuilder.isComponentInitialized(true);

        // Disable the domObserver for the entire stage
        $.async({
            component: pageBuilder,
            selector: pageBuilder.stageSelector
        }, pageBuilder.disableDomObserver.bind(pageBuilder));

        if (!pageBuilder.wysiwygConfigData()['pagebuilder_button'] ||
            pageBuilder.wysiwygConfigData()['pagebuilder_content_snapshot']) {
            pageBuilder.visiblePageBuilder(true);
        }
    }

    return select.extend({
        loadTemplate: function () {
            $.ajax({
                type: 'POST',
                url: this.loadTemplateUrl,
                data: {template_id: uiRegistry.get('index = popup_template_id').value()},
                showLoader: true,
                dataType: 'json',
                success: function (data) {
                    let element = uiRegistry.get('index = html_content');
                    element.value(data);
                    try {wysiwygAdapter.setContent(data)} catch (e) {}
                    try {reloadPageBuilder(element)} catch (e) {}
                }
            }).fail(function (error) {
            });
        }
    });
});
