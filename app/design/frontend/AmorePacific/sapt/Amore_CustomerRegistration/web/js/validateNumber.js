
 define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/validation'
], function ($) {
    'use strict';
    $.fn.inputFilter = function(callback, errMsg) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop focusout", function(e) {
          if (callback(this.value)) {
            // Accepted value
            if (["keydown","mousedown","focusout"].indexOf(e.type) >= 0){
              $(this).removeClass("input-error");
              this.setCustomValidity("");
            }
            this.oldValue = this.value;
            this.oldSelectionStart = this.selectionStart;
            this.oldSelectionEnd = this.selectionEnd;
          } else if (this.hasOwnProperty("oldValue")) {
            // Rejected value - restore the previous one
            $(this).addClass("input-error");
            this.setCustomValidity(errMsg);
            this.reportValidity();
            this.value = this.oldValue;
            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
          } else {
            // Rejected value - nothing to restore
            this.value = "";
          }
        });
    };
    /**
     * @api
     */
    $.widget('mage.validateNumber', {
        options: {
        },
        _init: function() {
        },
        /**
         * @private
         */
        _create: function () {
            $(document).ready(function() {
                $(".validate-number").inputFilter(function(value) {
                  return /^\d*$/.test(value);
                },"");
            });
        }
    });

    return $.mage.validateNumber;
});
