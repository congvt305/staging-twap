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
                AP_REVIEW_PICTURE = $(this).find('input[name="review_images[]"]')[0].files.length;
                AP_REVIEW_CONTENT = $(this).find('textarea[name=detail]').val();
                AP_REVIEW_RATING = $(this).find('input[name^=ratings]:checked').data('star-value');

				window.dataLayer.push({'event': 'review'});

				$(this).find('.submit').attr('disabled', true);
				return true;
			}
			return false;
		});
	};
});
