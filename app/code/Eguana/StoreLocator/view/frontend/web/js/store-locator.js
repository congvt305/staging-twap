define([
    'jquery',
    'map-viewer'
], function ($) {
    return function (config) {
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

        /**
         * Reset interval time
         */
        function appendMapTimer() {
            clearInterval(appendTimer);
            $(".store-list.selected-store .inner-store").append(htmlMapElement);
        }
        let windowSize = $(window).width();
        let htmlMapElement, appendTimer;
        function laodMobileView(x) {
            if (x.matches) {
                let mapHtmlParent = $(".stores-map").parent();
                htmlMapElement = mapHtmlParent.children();
                $("#store_map_viewer").css({"max-height":"600px"});
                $('li.store-list').children('.inner-store').first().append(htmlMapElement);
                $('.inner-store:not(:first-of-type)').css('display', 'block');
                $('.store-list:first-of-type').addClass('current-li' , 300, "easeOutSine" );
                $('.store-list:first-of-type').addClass('selected-store', 300 , "easeOutSine" );
                $('li.store-list').on('click', function() {
                    if ($(this).hasClass('current-li')){
                        $(this).removeClass('current-li',300, "easeOutSine" );
                        $(this).find('.inner-store').slideUp(300, "easeOutSine");
                    } else {
                        $(".current-li").not(this).removeClass("current-li",300, "easeOutSine").find('.store-info').find('.inner-store').slideUp(300, "easeOutSine");
                        $(this).toggleClass('current-li',300, "easeOutSine" ).find('.store-info').find('.inner-store').slideToggle(300, "easeOutSine");
                        $(this).find('.inner-store').slideDown(300, "easeOutSine");
                    }
                });
            } else {
                $( document ).ready(function() {
                    $('li.store-list').first().trigger('click');
                });
            }
        }
        if (config.locations) {
            multi_map_initialize(config.locations, config.zoom, config.viewStore, config.markerImages, config.north, config.south, config.west, config.east);
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
        let x = window.matchMedia("(max-width: 959px)");
        laodMobileView(x); // Call listener function at run time
        x.addListener(laodMobileView); // Attach listener function on state changes
    };
});
