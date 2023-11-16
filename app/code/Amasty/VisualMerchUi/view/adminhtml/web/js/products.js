/*global $H */
/*global $$ */
/*global jQuery */
/*@api*/
define([
    'jquery',
    'mage/template',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'mage/validation',
    'prototype',
    'Amasty_VisualMerchUi/js/pager',
    'mage/mage'
], function ($, mageTemplate, $translate) {
    'use strict';

    $.widget('mage.ammerchuiProducts', {
        options: {
            selectors: {
                view: '[data-role="page_products"]',
                addProductsContent: '[data-role="merchandiser_assign_products_content"]',
                productList: '[data-ammerchui-js="product-list"]',
                productItem: '[data-ammerchui-js="product-item"]',
                displayModeCheckbox: '[name="amlanding_is_dynamic"]',
                addProductsButton: '#am-add-products-button',
                switchButton: '[data-ammerchui-js="switch-button"]',
                setPageButton: '[data-ammerchui-js="set-page"]',
                setPageInput: '[data-ammerchui-js="page-input"]',
                moveTopButton: '[data-ammerchui-js="move-top"]',
                displayModeButton: '[data-ammerchui-js="display-mode"]',
                removeProductButton: '[data-ammerchui-js="remove-product"]'
            },
            isDynamicDisplayMode: false,
            savePositionsUrl: null,
            searchProductsUrl: null,
            currentPageId: null,
            formSelector: null,
            assignProductsUrl: null,
            addProductsUrl: null,
            removeProductUrl: null
        },
        sourcePosition: null,
        sourceIndex: null,
        view: null,

        _create: function () {
            var options = this.options;

            this.view = $(options.selectors.view);
            this.view.trigger('contentUpdated');
            this.setupView();
            this.initViewEventHandlers();
            this.updateProductsByConditions();

            if ($(options.selectors.displayModeCheckbox).prop('checked')) {
                $(options.selectors.addProductsButton).hide();
            } else {
                $(options.selectors.addProductsButton).show();
            }

            this.view.on(
                'contentUpdated', function () {
                    this.setupView();
                }.bind(this)
            );

            $('#am-products-sort').on('click', function () {
                this.savePositions(function () {
                    this.reloadView();
                }.bind(this));
            }.bind(this));

            $('select.store').on('change', function () {
                this.updateProducts(function () {
                    this.reloadView();
                }.bind(this));
            }.bind(this));

            $(options.selectors.addProductsContent).modal({
                title: $translate('Add Products'),
                opened: $.proxy(this.onAddProductsDialogOpen, this),
                buttons: [{
                    text: $translate('Save and Close'),
                    class: '',
                    click: $.proxy(this.addProducts, this)
                }]
            });

            $('#am-add-products-button').on('click', function () {
                $(options.selectors.addProductsContent).modal('openModal');
            }.bind(this));

            this.setupSearch();
            $(document).on('merchandiser:DisplayModeChanged', $.proxy(this.reloadView, this));
            var displayMode = $('[name="amlanding_is_dynamic"]').attr('checked') == 'checked';

            if (options.isDynamicDisplayMode != displayMode) {
                this.reloadView();
            }
        },

        onAddProductsDialogOpen: function () {
            $.ajax({
                type: 'GET',
                url: this.options.assignProductsUrl,
                context: $('body'),
                showLoader: true,
                success: function (data) {
                    $(this.options.selectors.addProductsContent).html(data);
                }.bind(this)
            });
        },

        addProducts: function () {
            $.ajax({
                type: 'POST',
                url: this.options.addProductsUrl,
                data: {product_ids: JSON.parse($('[name="category_products"]').val())},
                context: $('body'),
                showLoader: true
            }).success(function () {
                $(this.options.selectors.addProductsContent).modal('closeModal');
                this.reloadView();
            }.bind(this));
        },

        removeProduct: function (event) {
            var product = $(event.target).parents('li'),
                data = {
                    'page': this.getPage(this.view),
                    'remove_product_data': {
                        entity_id: product.find('input[name=entity_id]').val(),
                        source_position: product.find('input[name=position]').val()
                    },
                    'store_id': $('select.store').val(),
                    'sort_order': $('select.sort_order').val()
                };

            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: this.options.removeProductUrl,
                data: data,
                context: $('body'),
                showLoader: true
            }).success(function () {
                this.reloadView();
            }.bind(this));
        },

        initViewEventHandlers: function () {
            this.view.on('change', this.options.selectors.displayModeButton, this.changeDisplayMode.bindAsEventListener(this));
            this.view.on('click', this.options.selectors.moveTopButton, this.moveToTop.bindAsEventListener(this));
            this.view.on('click', this.options.selectors.removeProductButton, this.removeProduct.bindAsEventListener(this));
            this.view.on('click', this.options.selectors.setPageButton, this.changePage.bindAsEventListener(this));
            this.view.on('mousedown mouseup', '[data-amlanding-js="am-switch-button"], ' + this.options.selectors.moveTopButton, this.clickEffect.bindAsEventListener(this));
        },

        reloadView: function () {
            $(this.view).amvisualmerchProductsPager('reload');
        },

        setupView: function () {
            var sortableParent = this.view.find(this.options.selectors.productList),
                data,
                sortableObject;

            if (!sortableParent.length) {
                return;
            }

            $(this.view).parent().show();

            sortableParent.sortable({
                distance: 8,
                tolerance: 'pointer',
                cancel: 'input, button',
                forcePlaceholderSize: true,
                update: this.sortableDidUpdate.bind(this),
                start: this.sortableStartUpdate.bind(this)
            });
            data = {};
            sortableObject = sortableParent.data('sortable') || sortableParent.data('uiSortable');
            sortableObject.items.each(function (instance) {
                var key = $(instance.item).find('input[name=entity_id]').val();

                data[key] = $(instance.item);
            });

            sortableParent.data('item_id_mapper', data);
        },

        setupSearch: function () {
            $('#am-products-search-button').on('click', $.proxy(this.performSearch, this));
            $('#am-products-search').on('keypress', function (event) {
                var keyCode = event.keyCode || event.which;
                if (keyCode === Event.KEY_RETURN) {
                    event.stopPropagation();
                    event.preventDefault();
                    this.performSearch();
                }
            }.bind(this));
        },

        performSearch: function () {
            $('#am-products-search-form').validation({
                "rules": {
                    'am-products-search': {
                        "required": true
                    }
                }
            });
            if ($('#am-products-search-form').validation('isValid')) {
                var data = {
                    'limit': $('[name="limit"]').val(),
                    'store_id': $('select.store').val(),
                    'sort_order': $('select.sort_order').val(),
                    'search_query': $('#am-products-search').val()
                };

                $.ajax({
                    type: 'POST',
                    url: this.options.searchProductsUrl,
                    data: data,
                    context: $('body'),
                    showLoader: true
                }).success(function (data, textStatus, transport) {
                    $(this.view).amvisualmerchProductsPager('ajaxSuccess', data, textStatus, transport);
                    if ($(".search-result").length) {
                        $('body, html').animate({
                            scrollTop: $(".search-result").first().offset().top
                        }, 2000);
                    }
                }.bind(this));
            }
        },

        getSortedPositionsFromData: function (sortData) {
            // entity_id => pos
            var sortedArr = [];

            sortData.each(Array.prototype.push.bindAsEventListener(sortedArr));
            sortedArr.sort(this.sortArrayAsc.bind(this));

            return sortedArr;
        },

        getPage: function (view) {
            var parentView = $(view).parents('.ammerchui-tab');

            return parseInt(parentView.find('input[name=page]').val(), 10);
        },

        getPageSize: function (view) {
            var parentView = $(view).parents('.ammerchui-tab');

            return parseInt(parentView.find('select[name=limit]').val(), 10);
        },

        getStartIdx: function (view) {
            var perPage = this.getPageSize(view);

            return this.getPage(view) * perPage - perPage;
        },

        sortableDidUpdate: function (event, ui) {
            this.populateFromIdx(ui.item.parents('.ui-sortable').children());
            ui.item.find("input[type=checkbox]").prop("checked", true);
            this.sortDataObject();
            this.changeDisplayModeLabel(ui.item.find("input[type=checkbox]"));
        },

        moveItemInView: function (view, from, to) {
            var items = view.find('>*');

            if (to > from) {
                $(items.get(from)).insertAfter($(items.get(to)));
            } else {
                $(items.get(from)).insertBefore($(items.get(to)));
            }
            items.removeClass('selected');
            this.populateFromIdx(items);
        },

        sortableStartUpdate: function (event, ui) {
            ui.item.data('originIndex', ui.item.index());
        },

        changeDisplayMode: function (event) {
            var checkbox = $(event.target);

            this.changeDisplayModeLabel(checkbox);
            if (checkbox.is(':checked')) {
                this.sortDataObject();
            } else {
                var product = checkbox.parents('li'),
                    productDataObject = {
                        entity_id: product.find('input[name=entity_id]').val(),
                        source_position: product.find('input[name=position]').val()
                    };
                this.saveProductAutomaticMode(productDataObject);
                this.initSortDataObject();
            }
        },

        changeDisplayModeLabel: function (checkbox) {
            var currentItem = checkbox.closest(this.options.selectors.productItem),
                labelText = currentItem.find('[data-ammerchui-js="label-text"]'),
                switchButton = checkbox.closest(this.options.selectors.switchButton);

            if (checkbox.is(':checked')) {
                currentItem.addClass('-manual');
                labelText.text($translate('Pinned'));
                switchButton.prop('title', $translate('Enable Auto Sorting'));
            } else {
                currentItem.removeClass('-manual');
                labelText.text($translate('Auto'));
                switchButton.prop('title', $translate('Enable Manual Sorting'));
            }
        },

        moveToTop: function (event) {
            var product = $(event.currentTarget).parents('li'),
                input = product.find('input[name=position]'),
                checkbox = product.find(this.options.selectors.displayModeButton),
                idx = parseInt(product.index(), 10),
                pos = idx + this.getStartIdx($(input));

            event.preventDefault();
            product.find("input[type=checkbox]").prop("checked", true);

            if (!this.isValidPosition(pos)) {
                this.sourcePosition = null;
                this.sourceIndex = null;
            } else {
                this.sourcePosition = pos;
                this.sourceIndex = idx;
            }

            input.val(0);
            this.changePosition(input);
            this.changeDisplayModeLabel(checkbox);
        },

        clickEffect: function (event) {
            var button;

            if (event.which == 1) {
                if ($(event.target).hasClass('ammerhui-button')) {
                    button = $(event.target);
                    button.toggleClass('-pressed');
                } else {
                    button = $(event.target).parents('.ammerhui-button');
                    button.toggleClass('-pressed');
                }
            }
        },

        changePosition: function (input) {
            var destinationPosition = parseInt(input.val(), 10),
                destinationIndex = destinationPosition - this.getStartIdx(input),
                product = $(input).parents('li'),
                totalIndex = parseInt($('#catalog_category_products-total-count').text(), 10) - 1;

            if (destinationPosition > totalIndex) {
                input.val(totalIndex);
                this.changePosition(input);

                return;
            }

            // Moving within current page
            if (this.isValidPosition(this.sourcePosition)
                && this.isValidPosition(destinationPosition)
                && this.sourcePosition !== destinationPosition
            ) {
                // Move on all views
                this.element.find('.ui-sortable').each(function (idx, item) {
                    this.moveItemInView($(item), this.sourceIndex, destinationIndex);
                }.bind(this));

                this.sortDataObject();

                return;
            }

            // Moving off the current page
            if (
                this.isValidPosition(this.sourcePosition) &&
                destinationPosition >= 0 &&
                this.sourcePosition !== destinationPosition
            ) {
                var productDataObject = {
                    entity_id: product.find('input[name=entity_id]').val(),
                    source_position: this.sourcePosition
                };
                this.saveTopPosition(productDataObject);
            }
        },

        changePage: function (event) {
            var options = this.options,
                currentItem = $(event.target).closest(options.selectors.productItem),
                entityId = currentItem.find('input[name=entity_id]').val(),
                setPageInput = currentItem.find(options.selectors.setPageInput),
                index = parseInt(currentItem.index(), 10),
                pos = index + this.getStartIdx(setPageInput);

            if (this.pageValidation(setPageInput)) {
                var productsCount = parseInt($('[data-ui-id="product-listing-total-count"]').text()),
                    nextPosition = (setPageInput.val() * this.getPageSize(this.view)) - 1, // get last position on chosen page
                    productData = {
                        destination_position: nextPosition > productsCount ? productsCount : nextPosition,
                        entity_id: entityId,
                        source_position: pos
                    },
                    data = {
                        'page': this.getPage(this.view),
                        'move_product_data': productData,
                        'store_id': $('select.store').val(),
                        'sort_order': $('select.sort_order').val()
                    };

                $.ajax({
                    type: 'POST',
                    url: this.options.savePositionsUrl,
                    data: data,
                    context: $('body'),
                    showLoader: true
                }).success(function () {
                    this.reloadView();
                }.bind(this));
            }
        },

        populateFromIdx: function (items) {
            var startIdx = this.getStartIdx(items);

            items.find('input[name=position]').each(function (idx, item) {
                $(item).val(startIdx + idx);
            });
        },

        isValidPosition: function (pos) {
            var view = this.view.find('>*:eq(0)'),
                maxPos = this.getPage(view) * this.getPageSize(view),
                minPos = this.getStartIdx(view);

            return pos !== null && pos >= minPos && pos < maxPos;
        },

        pageValidation: function (input) {
            var nextVal = +input.val(),
                currentValue = parseInt(input.attr('current')),
                isValid = nextVal >= input.attr('min') && nextVal <= input.attr('max') && nextVal !== currentValue,
                popUp = $('<span class="ammerchui-valdation-popup"></span>');

            if (nextVal < input.attr('min')) {
                nextVal = input.attr('min');
                popUp.html($.mage.__('Min value is "%1"').replace('%1', input.attr('min')));
            }

            if (nextVal > input.attr('max')) {
                nextVal = input.attr('max');
                popUp.html($.mage.__('Max value is "%1"').replace('%1', input.attr('max')));
            }

            if (nextVal === currentValue) {
                popUp.html($.mage.__('Please choose page other than the current'));
            }

            if (!isValid) {
                input.addClass('-error-validation');
                input.after(popUp);

                setTimeout(function () {
                    input.removeClass('-error-validation').val(nextVal);
                    popUp.remove();
                }, 3000);

                return false;
            }

            return true;
        },

        sortArrayAsc: function (a, b) {
            var sortData = this.getSortData(),
                keyA = sortData.get(a.key),
                keyB = sortData.get(b.key),
                diff = parseFloat(a.value) - parseFloat(b.value);

            if (diff !== 0) {
                return diff;
            }

            if (keyA === undefined && keyB !== undefined) {
                return -1;
            }

            if (keyA !== undefined && keyB === undefined) {
                return 1;
            }

            return 0;
        },

        getSortData: function () {
            return $H(JSON.parse($('#vm_landing_products').val()));
        },

        sortDataObject: function (event) {
            this.initSortDataObject();
            this.savePositions();
        },

        initSortDataObject: function () {
            var data = this.getSortData(),
                sorted = $H(),
                sortedNew = $H(),
                uiSortable = this.view.find('.ui-sortable'),
                startIdx = this.getStartIdx(uiSortable),
                sortedArr = this.getSortedPositionsFromData(data);

            // Pre-sort all items
            sortedArr.each(function (item, idx) {
                sorted.set(item.key, String(idx));
            });

            $(uiSortable).find('> *').each(function (idx, item) {
                var entityId = $(item).find('[name=entity_id]').val();
                sorted.set(entityId, String(startIdx));
                if ($(item).find('input:checked').length) {
                    sortedNew.set(entityId, String(startIdx));
                }
                startIdx++;
            });

            $('#vm_landing_products_manual').val(Object.toJSON(sortedNew));
            $('#vm_landing_products').val(Object.toJSON(sorted));
            return sorted;
        },

        savePositions: function (callback) {
            var data = {
                    'page': this.getPage(this.view),
                    'positions': JSON.parse($('#vm_landing_products_manual').val()),
                    'store_id': $('select.store').val(),
                    'sort_order': $('select.sort_order').val()
                },
                showLoader = typeof callback !== 'undefined';

            $.ajax({
                type: 'POST',
                url: this.options.savePositionsUrl,
                data: data,
                context: $('body'),
                showLoader: showLoader
            }).success(function () {
                if (callback) {
                    callback();
                }
            });
        },

        updateProducts: function (callback) {
            var data = {
                    'page': this.getPage(this.view),
                    'store_id': $('select.store').val(),
                    'sort_order': $('select.sort_order').val()
                },
                showLoader = typeof callback !== 'undefined';

            $.ajax({
                type: 'POST',
                url: this.options.savePositionsUrl,
                data: data,
                context: $('body'),
                showLoader: showLoader
            }).success(function () {
                if (callback) {
                    callback();
                }
            });
        },

        saveProductAutomaticMode: function (productData) {
            var data = {
                'page': this.getPage(this.view),
                'automatic_product_data': productData,
                'store_id': $('select.store').val(),
                'sort_order': $('select.sort_order').val()
            };

            $.ajax({
                type: 'POST',
                url: this.options.savePositionsUrl,
                data: data,
                context: $('body'),
                showLoader: true
            }).success(function () {
                this.reloadView();
            }.bind(this));
        },

        saveTopPosition: function (productData) {
            var data = {
                'page': this.getPage(this.view),
                'top_product_data': productData,
                'store_id': $('select.store').val(),
                'sort_order': $('select.sort_order').val()
            };

            $.ajax({
                type: 'POST',
                url: this.options.savePositionsUrl,
                data: data,
                context: $('body'),
                showLoader: true
            }).success(function () {
                this.reloadView();
            }.bind(this));
        },

        updateProductsByConditions: function () {
            $('[data-index="conditions_fieldset"]').on('click', '#am-apply-conditions', function () {
                $.ajax({
                    type: 'POST',
                    url: this.options.savePositionsUrl,
                    data: $('#container').find('[name^=rule]').serialize(),
                    context: $('body'),
                    showLoader: true
                }).success(function () {
                    this.reloadView();
                }.bind(this));
            }.bind(this));
        }
    });

    return $.mage.ammerchuiProducts;
});
