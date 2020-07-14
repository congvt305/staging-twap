define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'domReady!'
    ], function($, modal) {
    'use strict';

    var modaloption = {
        type: 'popup',
        modalClass: 'modal-popup',
        responsive: true,
        innerScroll: true,
        clickableOverlay: true
    };

    return function(config, element) {
        $( document ).ready(function() {

            $('#is_subscribed-read-policy').on('click', function (e) {
                e.preventDefault();
                var privacyPolicyPopupSelector = '.news-letter-policy-popup';
                var privacyPolicyLabelSelector = '.news-letter-policy-label';
                var chceckBoxSelector = '#is_subscribed';

                modaloption['title'] = $(privacyPolicyLabelSelector).text();

                modaloption['buttons'] = [
                    {
                        text: $.mage.__('Agree'),
                        class: 'agree button action primary',
                        click: function () {
                            this.closeModal();
                            $(chceckBoxSelector).prop("checked", true);
                        }
                    },
                    {
                        text: $.mage.__('Disagree'),
                        class: 'disagree button action secondary',
                        click: function () {
                            this.closeModal();
                            $(chceckBoxSelector).prop("checked", false);
                            return false;
                        }
                    }
                ]
                $(privacyPolicyPopupSelector).modal(modaloption).modal('openModal');
            });

            $('#region_id').on('click', function (e) {
                var selectedRegion = $('#region_id option:selected').text();
                $('#dm_state').val(selectedRegion);
            });
        });

    };

});