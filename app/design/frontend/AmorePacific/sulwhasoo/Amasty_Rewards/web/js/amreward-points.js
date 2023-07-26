/*jshint browser:true jquery:true*/
define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    'mage/url',
], function($, modal, url){
    "use strict";

    $.widget('mage.amrewardPoints', {
        options: {
        },
        _create: function () {
            this.rewardAmount = $(this.options.rewardAmount);

            this.removeReward = $(this.options.removeRewardSelector);

            $(this.options.applyButton).on('click', $.proxy(function () {
                var self = this,
                    form = $('#discount-reward-form');
                this.rewardAmount.attr('data-validate', '{required:true}');
                this.removeReward.attr('value', '0');
                $(this.element).validation()
                $.ajax({
                    url: url.build('cj_amrewards/ajax/validaterewardpost'),
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: form.serialize(),
                    beforeSend: function () {
                        $('body').loader('show');
                    },
                    success: function (response) {
                        if (response['success']) {
                            $(self.element).submit()
                        } else {
                            form.find('.message-error').html(response['message']).show();
                            setTimeout(function() {
                                form.find('.message-error').hide()
                            }, 3000)
                        }
                    },
                    /** @inheritdoc */
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            }, this));

            $(this.options.cancelButton).on('click', $.proxy(function () {
                this.rewardAmount.removeAttr('data-validate');
                this.removeReward.attr('value', '1');
                this.element.submit();
            }, this));

            if (!this.isGreaterThanMinimumBalance()) {
                this.getMinimumRewardNoteDOM().show();
                this.disableRewardInput();

            }
        },

        /**
         *
         * @returns {boolean}
         */
        isGreaterThanMinimumBalance: function () {
            var realBalance = this.options.customerBalance;

            if (this.options.usedPoints) {
                realBalance += this.options.usedPoints;
            }

            return !this.options.minimumBalance || (realBalance >= this.options.minimumBalance);
        },

        /**
         * @return void
         */
        disableRewardInput: function() {
            $(this.options.applyButton).prop("disabled", true);
            $(this.options.rewardAmount).prop("disabled", true);
        },

        /**
         *
         * @returns {*|jQuery|HTMLElement}
         */
        getMinimumRewardNoteDOM: function() {
            return $(this.options.minimumNoteSelector);
        }
    });

    return $.mage.amrewardPoints;
});
