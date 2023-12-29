define([
    'jquery',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal'
], function ($, $t, customerData, modal) {
    'use strict';

    $.widget('magepow.ajaxcart', {
        options: {
            processStart: null,
            processStop : null,
            bindSubmit  : true,
            showLoader  : true,
            minicartSelector: '[data-block="minicart"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: '',
            addToCartButtonTextAdded: '',
            addToCartButtonTextDefault: '',
            addUrl: '',
            quickview: false,
            isProductView: false,
            isSuggestPopup: false,
            quickViewUrl: ''
        },

        productIdInputName: ["product", "product-id", "data-product-id", "data-product"],

        _create: function () {
            var self = this;
            customerData.reload(['wishlist'], true);
            customerData.get('wishlist').subscribe(function(wishlist) {
                self._updateWishlistIcons(wishlist);
            });

            self._initAjaxcart();
            self._initQtyChange();
            window.ajaxCart = self;
            $('body').on('ajaxcart:refresh', function () {
                window.ajaxCart._initAjaxcart();
            });
        },

        _initQtyChange: function() {
            $('input[name=qty]').on('change', function() {
                var priceSelector = $(this).closest('.product-info-main').find('.price-box');
                priceSelector.trigger('reloadPrice');
            });
        },

        _initAjaxcart: function () {
            var options = this.options;
            var self = this;

            self.element.off('click').on("click", options.addToCartButtonSelector, function(e){
                if ($(window).width() < 768 && options.isProductView) {
                    if ($(e.target).closest('.product-info-main').length &&
                        (!$('.product-info-main-wrapper').hasClass('is_popupcart') && $(e.target).closest('.product-info-main').hasClass('sticky'))) {
                        return;
                    }
                }

                var form = $(this).parents('form').get(0);
                if($(form).hasClass('reorder')) return;// turn off the recently ordered sidebar in category page
                e.preventDefault();

                var data = '';
                if (form && !$(this).hasClass('wishlist-item')) {
                    var isValid = true;
                    if (options.isProductView || $('body').hasClass('open-quickview')) {
                        try {
                            isValid = $(form).valid();
                        } catch(err) {
                            isValid = true;
                        }
                    }

                    if (isValid) {
                        var oldAction = $(form).attr('action');
                        var serialize = $(form).serialize();
                        var id = self._findId(this, oldAction, form);

                        if ($.isNumeric(id)) {
                            data += 'id=' + id;
                            if (serialize == '') {
                                $(form).find('input, select').each(function () {
                                    data += "&" + $(this).attr('name') + "=" + $(this).val();
                                });
                            } else {
                                data += "&" + serialize;
                            }

                            if (options.quickview) {
                                var isWishlist = false;
                                if (window.location.pathname == '/wishlist/'){
                                    isWishlist = true;
                                }
                                window.parent.ajaxCart._sendAjax(options.addUrl, data, oldAction, form, isWishlist);
                                return false;
                            }

                            self._sendAjax(options.addUrl, data, oldAction, form);
                            return false;
                        }

                        window.location.href = oldAction;
                    }
                } else {
                    var dataPost = $(this).data('post');
                    if (dataPost) {
                        var formKey = $("input[name='form_key']").val(),
                            oldAction = dataPost.action,
                            formData = new FormData();
                        formData.set('product', dataPost.data.product);
                        formData.set('form_key', formKey);
                        formData.set('uenc', dataPost.data.uenc);
                        self._sendAjax(options.addUrl, formData, oldAction, false, true);

                        return false;
                    } else {
                        var id = self._findId(this);
                        if (id) {
                            e.stopImmediatePropagation();
                            self.quickview(options.quickViewUrl + 'id/' + id);
                            return false;
                        }
                    }
                }
            });
        },

        _findId: function (btn, oldAction, form) {
            var self = this;
            var id = $(btn).attr('data-product-id');
            if($.isNumeric(id)) return id;
            var item = $(btn).closest('li.product-item');
            id = $(item).find('[data-product-id]').attr('data-product-id');
            if ($.isNumeric(id)) return id;

            if (oldAction) {
                var formData = oldAction.split('/');
                for (var i = 0; i < formData.length; i++) {
                    if (self.productIdInputName.indexOf(formData[i]) >= 0) {
                        if ($.isNumeric(formData[i + 1])) {
                            id = formData[i + 1];
                        }
                    }
                }

                if ($.isNumeric(id)) return id;
            }

            if (form) {
                $(form).find('input').each(function () {
                    if (self.productIdInputName.indexOf($(this).attr('name')) >= 0) {
                        if ($.isNumeric($(this).val())) {
                            id = $(this).val();
                        }
                    }
                });

                if ($.isNumeric(id)) return id;
            }

            var priceBox = $(btn).closest('.price-box.price-final_price');
            id = $(priceBox).attr('data-product-id');

            if ($.isNumeric(id)) return id;

            return false;
        },

        _sendAjax: function (addUrl, data, oldAction, form=false, isWishlist = false) {
            var body = $('body');
            var options = this.options;
            var self = this;
            if(form){
                self.disableAddToCartButton(form);
                data = new FormData(form);
            }
            $.ajax({
                type: 'post',
                url: addUrl,
                data: data,
                showLoader: options.showLoader,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if(!$('#modals_ajaxcart').length) body.append('<div id="modals_ajaxcart" style="display:none"></div>');
                    var _qsModal = $('#modals_ajaxcart');
                    if (data.popup) {
                        if (data.success) {
                            $(document).trigger('ajax:addToCart', {productIds: [data.id]});
                            setTimeout(function (){
                                if (isWishlist){
                                    window.location.reload();
                                }
                            }, 2000);
                        }
                        self._showPopup(_qsModal, '<div class="content-ajaxcart">' + data.popup + '</div>');
                    } else if (data.error && data.view) {
                        /*show Quick View*/
                        var quickView = true;
                        if(form){
                            var addToCartButtonMain = $(form).closest('#product_addtocart_form');
                            if(addToCartButtonMain.length) quickView = false;
                        }
                        if(quickView && data.error_info.search("not available") == -1){
                            if ($.fn.quickview) {
                                $.fn.quickview({url:options.quickViewUrl  + 'id/' + data['id']});
                            } else {
                                self.quickview({url:options.quickViewUrl  + 'id/' + data['id']});
                            }
                        } else {
                            self._showPopup(_qsModal, data.error_info);
                        }
                    }

                    if(form) self.enableAddToCartButton(form);
                },
                error: function () {
                    window.location.href = oldAction;
                }
            });
        },

        _showPopup: function (_qsModal, data) {
            var body = $('body');
            var self = this;
            _qsModal.html(data);
            if(!body.hasClass('open-ajaxcart')){
                self._closePopup();
                body.addClass('open-ajaxcart');
                var modals = body.find('.modals-ajaxcart');
                if(!modals.length){
                    modal({
                        type: 'popup',
                        modalClass: 'modals-ajaxcart',
                        responsive: true,
                        innerScroll: true,
                        buttons: false,
                        closed: function(){
                            body.removeClass('open-ajaxcart');
                        }
                    }, _qsModal);
                }
                _qsModal.modal('openModal');

                var wishlist = customerData.get('wishlist')();
                self._updateWishlistIcons(wishlist);
                _qsModal.trigger('contentUpdated');
            }
            _qsModal.find('.btn-continue').on('click', function() {
                self._closePopup();
            });
        },

        _closePopup: function(){
            var $body = $('body');
            $body.removeClass('open-minicart-modal open-ajaxcart open-quickview _has-modal');
            $body.find('.modal-popup, .modals-overlay').remove();
        },

        /**
         * @param {String} form
         */
        disableAddToCartButton: function (form) {
            var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
            addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
            addToCartButton.attr('title', addToCartButtonTextWhileAdding);
        },

        /**
         * @param {String} form
         */
        enableAddToCartButton: function (form) {
            var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(addToCartButtonTextAdded);
            addToCartButton.attr('title', addToCartButtonTextAdded);

            setTimeout(function () {
                var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');

                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextDefault);
                addToCartButton.attr('title', addToCartButtonTextDefault);
            }, 1000);
        },

        quickview: function () {
            var obj = arguments[0];
            var body = $('body');
            if(!$('#modals_quickview').length) body.append('<div id="modals_quickview" style="display:none"></div>');
            var _qsModal = $('#modals_quickview');
            var quickajax= function(url){
                $.ajax({
                    url:url,
                    type:'POST',
                    showLoader: true,
                    cache:false,
                    success:function(data){
                        _qsModal.html('<div class="content-quickview">' + data + '</div>');
                        if(!body.hasClass('open-quickview')){
                            body.addClass('open-quickview');
                            var modalsQuickview = body.find('.modals-quickview');
                            if(!modalsQuickview.length){
                                modal({
                                    type: 'popup',
                                    modalClass: 'modals-quickview',
                                    responsive: true,
                                    innerScroll: true,
                                    buttons: false,
                                    closed: function(){
                                        body.removeClass('open-quickview');
                                    }
                                }, _qsModal);
                            }
                            _qsModal.modal('openModal');
                        }
                        _qsModal.trigger('contentUpdated');
                    }
                });
                _qsModal.on('fotorama:load', function(){
                    _qsModal.find(".product-view .product-info-main.product-shop").height(_qsModal.find(".product-img-box").height());
                });
            }
            if(obj.url){
                quickajax(obj.url)
            } else {
                $(document).on('click', obj.itemClass, function(e) {
                    e.preventDefault();
                    quickajax($(this).data('url'))
                });
            }
        },

        /**
         * Update all related product's wishlist icons in quick view popup
         *
         * @param wishlist
         * @private
         */
        _updateWishlistIcons: function (wishlist) {
            var wishlistItems = wishlist && wishlist['all_wishlist_items'] ? wishlist['all_wishlist_items'] : {}
            $(`.content-ajaxcart .action.towishlist`).each(function () {
                var productId = $(this).data('product-id');
                if (wishlistItems.hasOwnProperty(productId)) {
                    $(this).addClass('wishlisticon');
                } else {
                    $(this).removeClass('wishlisticon');
                }
            });
        }

    });

    return $.magepow.ajaxcart;
});
