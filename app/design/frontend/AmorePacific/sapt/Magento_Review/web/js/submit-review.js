/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
	'jquery'
], function ($) {
	'use strict';

	return function (config, element) {
		$(element).on('submit', function (e) {
			if ($(this).valid()) {
                var ratingElement = $(this).find('input[name^=ratings]:checked'),
                    ratingValue = undefined;
                if (ratingElement.length) {
                    var ratingElementIdArr = ratingElement.attr('id')?.split('_');

                    if (ratingElementIdArr.length) {
                        ratingValue = ratingElementIdArr.pop();
                    }
                }

                AP_REVIEW_PICTURE = $(this).find('input[name="review_images[]"]')[0].files.length;
                AP_REVIEW_CONTENT = $(this).find('textarea[name=detail]').val();
                AP_REVIEW_RATING = ratingValue;

				window.dataLayer.push({'event': 'review'});

				$(this).find('.submit').attr('disabled', true);
				return true;
			}
			return false;
		});
	};
});
