define(['jquery','Amasty_Coupons/js/action/apply-coupon-codes', 'Amasty_Coupons/js/model/coupon'],
    function ($,setCouponCodesAction, couponModel) {
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
                        this.isApplied(this.responseProcessor.appliedCoupons.length > 0);
                        //window.location.reload();
                    }.bind(this))
                    .always(function () {
                        this.isLoading(false);
                    }.bind(this));
            }
        },
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                console.log('when cancel coupon')
                cancelCouponAction(isApplied);
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
