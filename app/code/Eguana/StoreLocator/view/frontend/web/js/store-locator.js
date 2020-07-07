define([
    'jquery',
    'jquery/ui',
    'map-viewer'
], function ($) {
    return function (config) {
        //check for location. if location exist then initialize map
        // for plus icon click on store
        $('.store-list').on('click', function () {
            let storeInfo;
            storeInfo = $(this).children('.store-info');
            if (!$(this).hasClass('selected-store')) {
                $(this).closest('.stores-listing-ul').children('li').each(function () {
                    let store = $(this).children('.store-info');
                    $(this).removeClass('selected-store');
                    store.children('.store-secondary-info').slideUp();
                });
                $(this).closest('.store-list').addClass('selected-store');
                storeInfo.children('.store-secondary-info').slideDown();
            }

            appendTimer = setInterval(appendMapTimer, 100);
        });

        function appendMapTimer() {
            clearInterval(appendTimer);

            //$(".store-list .inner-store").empty();
            $(".store-list.selected-store .inner-store").append(htmlMapElement);
        }
        var windowSize = $(window).width();
        var htmlMapElement, appendTimer;
        if (windowSize <= 959) {

            var mapHtmlParent = $(".stores-map").parent();
            htmlMapElement = mapHtmlParent.children();
            //mapHtmlParent.empty();
            console.log(htmlMapElement);

            $("#store_map_viewer").css({"height":"108.333vw"});

            $('li.store-list').first().find('.inner-store').css({"display":"block"});
            $('li.store-list').children('.inner-store').first().append(htmlMapElement);
            //$('li.store-list').children('.inner-store').slideUp();
            $('li.store-list').find('span.accordion-icon').css('opacity', '0');
            $('li.store-list').click(function(e) {
                $(this).find('span.accordion-icon').css('opacity', '0.4');
                $('li.store-list').children('.inner-store').slideUp();
                // $(this).first().trigger('click');
                $(this).children('.inner-store').slideToggle();
            });
        } else {
            $( document ).ready(function() {
                $('li.store-list').first().trigger('click');
            });
        }

        if (config.locations) {
            multi_map_initialize(config.locations, 8, config.viewStore, config.markerImages);
        }
        pushmarker(0);

        $( document ).ready(function() {
            if ($('#current-lat').val().length != 0) {
                $('.map-open').attr('state-dir','1');
            }
        });

        /**
         * set direction for source to destination
         * @param longitude
         * @param latitude
         * @param link_with_destination
         */
        function setDirection(longitude, latitude, link_with_destination,element) {

            let url = link_with_destination.split('||');
            link_with_destination = url[0]+longitude  + ',' + latitude + '&destination='+url[1];
            element.attr('href', link_with_destination);
        }

        /**
         * expand map
         */
        $('.stores-map .expend').click(function () {
            $(this).closest('.stores-map').toggleClass('active');
        });
    };
});
