define(["uiComponent", "dataServicesBase", "jquery", "Magento_Catalog/js/price-utils", 'Magento_Customer/js/customer-data', 'ko', 'js/plugins/slick/slick'], function (
    Component,
    ds,
    $,
    priceUnits,
    customerData,
    ko
) {
    "use strict"
    return Component.extend({
        defaults: {
            template:
                "Magento_ProductRecommendationsLayout/recommendations.html",
            recs: [],
        },

        wishlistitem: ko.observable(),

        initialize: function (config) {
            this._super(config)
            this.pagePlacement = config.pagePlacement
            this.placeholderUrl = config.placeholderUrl
            this.priceFormat = config.priceFormat
            this.priceUnits = priceUnits
            this.currencyConfiguration = config.currencyConfiguration
            this.alternateEnvironmentId = config.alternateEnvironmentId
            this.wishlistitem = customerData.get('wishlist');
            return this
        },
        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this._super().observe(["recs"])
        },

        //Helper function to add addToCart button & convert currency
        /**
         *
         * @param {@} response is type Array.
         * @returns type Array.
         */
        processResponse(response) {
            const units = []
            if (!response.length) {
                return units
            }

            for (let i = 0; i < response.length; i++) {
                response[i].products = response[i].products.slice(
                    0,
                    response[i].displayNumber,
                )
                for (let j = 0; j < response[i].products.length; j++) {
                    if (response[i].products[j].productId) {
                        const form_key = $.cookie("form_key")
                        const url = this.createAddToCartUrl(
                            response[i].products[j].productId,
                        )
                        const postUenc = this.encodeUenc(url)
                        const addToCart = {form_key, url, postUenc}
                        response[i].products[j].addToCart = addToCart
                    }

                    if (
                        this.currencyConfiguration &&
                        response[i].products[j].currency !==
                        this.currencyConfiguration.currency
                    ) {
                        response[i].products[j].prices.minimum.final =
                            (response[i].products[j].prices &&
                                response[i].products[j].prices.minimum &&
                                response[i].products[j].prices.minimum.final)
                                ? this.convertPrice(response[i].products[j].prices.minimum.final)
                                : null;
                        response[i].products[
                            j
                            ].currency = this.currencyConfiguration.currency
                    }
                }
                units.push(response[i])
            }
            units.sort((a, b) => a.displayOrder - b.displayOrder)
            return units
        },

        loadJsAfterKoRender: function (self, unit) {
            const renderEvent = new CustomEvent("render", {detail: unit})
            document.dispatchEvent(renderEvent);

            var $prdRecomm = $('.product-recommendations-' + unit.unitId +' .product-items'),
                $prdRecommProgressBar = $('.product-recommendations-' + unit.unitId +' .progress .progress_ing'),
                $prdRecommSlidesToShow = 5;
            $prdRecomm.on('init', function(event, slick){
                (window.innerWidth > 768) ? $prdRecommSlidesToShow = 5 : $prdRecommSlidesToShow = 2;
                var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow));

                $prdRecommProgressBar.css({
                    'width': calc + '%',
                });
            });

            $prdRecomm.slick({
                dots:false,
                slidesToShow: 5,
                slidesToScroll: 5,
                infinite: false,
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            dots: false,
                            slidesToShow: 1.7,
                            slidesToScroll: 2,
                            prevArrow: false,
                            nextArrow: false
                        }
                    },
                ]
            }).on('beforeChange', function(event, slick, currentSlide, nextSlide){
                (window.innerWidth > 768) ? $prdRecommSlidesToShow = 5 : $prdRecommSlidesToShow = 2;
                var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow)) * ((nextSlide / $prdRecommSlidesToShow) + 1);

                $prdRecommProgressBar.css({
                    'width': calc + '%',
                });
            });
        },

        convertPrice: function (price) {
            return parseFloat(price * this.currencyConfiguration.rate)
        },

        createAddToCartUrl(productId) {
            const currentLocationUENC = encodeURIComponent(
                this.encodeUenc(BASE_URL),
            )
            const postUrl =
                BASE_URL +
                "checkout/cart/add/uenc/" +
                currentLocationUENC +
                "/product/" +
                productId
            return postUrl
        },

        encodeUenc: function (value) {
            const regex = /=/gi
            return btoa(value).replace(regex, ",")
        },
    })
})
