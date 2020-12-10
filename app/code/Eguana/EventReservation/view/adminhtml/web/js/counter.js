/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 4:04 PM
 */
define([
    "jquery",
    "jquery/ui",
    "prototype",
    "Magento_Ui/js/modal/alert",
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    "reloadGrid",
    "mage/calendar",
    "domReady!",
    "mage/translate",
], function ($, jUi, prototype, alert, confirm, modal, reloadGrid) {
    'use strict';

    $.Counterjs = function()
    {
        this.KC_value = '';
    };

    $.Counterjs.prototype = {
        init: function (counterurl)
        {
            window.counterUrl = counterurl;
            return this;
        },
        showCounterList: function(eventId, counterId, counterurl, countersaveurl){
            window.counterId = counterId;
            window.eventId = eventId;
            var options = {
                type: 'slide',
                responsive: true,
                closeText: 'Close',
                modalClass: 'my-custom-class',
                autoOpen: false,
                clickableOverlay: true,
                modalVisibleClass: '_show',
                parentModalClass: 'customclass',
                innerScrollClass: '_inner-scroll',
                innerScroll: true,
                appendTo: 'body',
                wrapperClass: 'modals-wrapper',
                overlayClass: 'modals-overlay',
                responsiveClass: 'modal-slide',
                title: "Event Reservation Counter",
                buttons: [{
                            text: $.mage.__('Cancel'),
                            class: 'action-scalable cancel',
                            click: function () {
                                this.closeModal();
                            }
                        },
                        {
                            text: $.mage.__('Save'),
                            class: 'action-default primary',
                            click: function () {
                                if ($('form[id="form-counter"]').valid()) {
                                    let formkey = "<input name='form_key' value=" + window.FORM_KEY + " title='form_key' type='hidden'>";
                                    let eventid = "<input name='event_id' value=" + window.eventId + " title='event_id' type='hidden'>";
                                    let counterid = "<input name='counter_id' value=" + window.counterId + " title='counter_id' type='hidden'>";
                                    $('form[id="form-counter"]').append(formkey);
                                    $('form[id="form-counter"]').append(eventid);
                                    $('form[id="form-counter"]').append(counterid);
                                    let form_data = $('form[id="form-counter"]').serialize();
                                    $.ajax({
                                        url: countersaveurl,
                                        type: 'POST',
                                        data: form_data,
                                        beforeSend: function () {
                                            jQuery('body').loader('show');
                                        },
                                        success: function (data) {
                                            jQuery('body').loader('hide');
                                            if (data.success) {
                                                $(".action-close").trigger("click");
                                                reloadGrid.reloadUIComponent("event_counter_listing.event_counter_listing_data_source");
                                            } else {
                                                let errorMessage = 'Sorry something went wrong';
                                                if (data.errorMessage) {
                                                    errorMessage = data.errorMessage;
                                                }
                                                $("#counterModal").find(".counter-error-message").html($.mage.__(errorMessage)).show("slow");
                                            }
                                        },
                                        error: function (result) {
                                            jQuery('body').loader('hide');
                                            $("#counterModal").html($.mage.__('Unable to fetch counter record'));
                                        }
                                    });
                                }
                            }
                        }],
                /**
                 * Escape key press handler,
                 * close modal window
                 */
                escapeKey: function () {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal();
                    }
                }
            };
            $.ajax({
                url: counterUrl,
                type: 'POST',
                data : {form_key: window.FORM_KEY, eventId: window.eventId, counterId: window.counterId},
                beforeSend: function() {
                    jQuery('body').loader('show');
                },
                success: function(data){
                    jQuery('body').loader('hide');
                    $("#counterModal").find(".counter-error-message").hide();
                    $("#form-counter").html(data['output']);
                },
                error: function(result){
                    jQuery('body').loader('hide');
                    $("#counterModal").html($.mage.__('Unable to fetch counter record'));
                }
            });
            $('#counterModal').modal(options).modal('openModal');
            $("body").delegate(".modal-date-picker", "focusin", function() {
                $('#counter_from_date').datepicker({
                    changeYear:true,
                    changeMonth:true,
                    yearRange: "1970:2050",
                    buttonText:"Select Date",
                    dateFormat:"yy-mm-dd",
                    minDate: 0,
                    autoclose: true,
                    onSelect: function(date) {
                        var dates = date.split('-');
                        var lastDate = new Date(dates[0], dates[1]-1, dates[2]);

                        $('#counter_to_date').datepicker("option", "minDate", lastDate);
                    }
                });
                var fromDate = $('#counter_from_date').val();
                var date = fromDate.split('-');
                fromDate = new Date(date[0], date[1]-1, date[2]);
                $('#counter_to_date').datepicker({
                    changeYear:true,
                    changeMonth:true,
                    yearRange: "1970:2050",
                    buttonText:"Select Date",
                    dateFormat:"yy-mm-dd",
                    autoclose: true,
                    minDate: fromDate
                });
            });
            $("body").delegate(".modal-time-picker", "focusin", function() {
                $(this).timepicker({
                    stepMinute: 1,
                    dateFormat:'yy-mm-dd'
                });
            });
        },
    }
    return new $.Counterjs();
});
