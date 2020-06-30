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
            $(".store-list.selected-store .inner-store").append(htmlMapElement);
        }
        var windowSize = $(window).width();
        var htmlMapElement, appendTimer;
        if (windowSize <= 959) {
            var mapHtmlParent = $(".stores-map").parent();
            htmlMapElement = mapHtmlParent.children();
            // console.log(htmlMapElement);
            $("#store_map_viewer").css({"height":"108.333vw"});
            $(function() {
                $('li.store-list').removeClass('selected-store');
                $('.store-info').click(function(j) {

                    var dropDown = $(this).closest('li.store-list').find('.inner-store');
                    $(this).closest('.stores-listing-ul').find('.inner-store').not(dropDown).slideUp();

                    if ($(this).parent('li.store-list').hasClass('selected-store')) {
                        $(this).parent('li.store-list').removeClass('selected-store');
                    } else {
                        $(this).closest('.stores-listing-ul').find('li.store-list.selected-store').removeClass('selected-store');
                        $(this).parent('li.store-list').addClass('selected-store');
                    }

                    dropDown.stop(false, true).slideToggle("slow");
                    j.preventDefault();
                });
            });
        } else {
            $('li.store-list').first().trigger('click');
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
