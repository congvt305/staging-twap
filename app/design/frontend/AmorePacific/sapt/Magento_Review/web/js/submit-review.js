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
				AP_REVIE_PICTURE = $('#review-form [name="review_images[]"]').size();
				AP_REVIE_CONTENT = $('#review-form [id="review_field"]').val();
				AP_REVIE_RATING = $('#review-form [name^=ratings]').val();

				window.dataLayer.push({'event': 'review'});

				$(this).find('.submit').attr('disabled', true);
				return true;
			}
			return false;
		});
	};
});
