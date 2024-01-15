define([
	'jquery',
	'Amasty_Base/js/http_build_query',
	'uiRegistry',
	'underscore',
	'mage/cookies',
	'Magento_Ui/js/modal/modal'
], function ($, httpBuildQuery, uiRegistry, _) {
	'use strict';

	return function (widget) {

		$.widget('mage.amScrollScript', widget, {
			initialize: function () {
				var self = this,
					isValidConfiguration;

				this.next_data_cache = "";
				this.pagesLoaded = [];
				this._initPagesCount();
				this.disabled = 1;
				isValidConfiguration = this._validate();

				if (!isValidConfiguration) {
					$(this.classes.backToTopButton).remove(); //remove old nav bar

					return;
				}

				this.disabled = 0;
				this.type = this.options['actionMode'];
				this.pagesBeforeButton = this.options['pages_before_button'];
				this.currentPage = this._getCurrentPage();
				this.pagesLoaded.push(this.currentPage);

				if (this.type === 'button') {
					this._generateButton('before');
					this._generateButton('after');
				}

				this._preloadPages();

				setTimeout(function () {
					$(window).on('scroll', _.debounce(self._initPaginator.bind(self), 50));
					self._initPaginator();
				}, 3000);

				this._initBackToTop();
				this.initPageStepForwardListener(this.currentPage);
				this._pagePositionAfterStepBack();
			},
		});

		return $.mage.SwatchRenderer;
	}
});