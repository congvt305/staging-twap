define(['Amasty_Coupons/js/action/apply-coupon-codes', 'Amasty_Coupons/js/model/coupon'],
    function (setCouponCodesAction, couponModel) {
    'use strict';
    var mixin = {
        apply: function () {
            var codes = [];
            console.log('inside mixin');

            if (this.validate()) {
                this.isLoading(true);

                codes = codes.concat(this.couponsArray())
                    .concat(couponModel.renderCoupons(this.inputCode()));

                setCouponCodesAction(codes, this.responseProcessor)
                    .done(function () {
                        this.handleErrorMessages();
                        this.inputCode(this.responseProcessor.appliedCoupons.join(', '));
                        $('.totals.discount .title').removeClass('negative');
                    }.bind(this))
                    .always(function () {
                        this.isLoading(false);
                    }.bind(this));
            }
        },
    };
    return function (target) {
        return target.extend(mixin);
    };
});
