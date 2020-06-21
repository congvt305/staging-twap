define([
    'jquery',
    'jquery/ui',
    'map-viewer'
], function ($) {
    return function (config) {
        //check for location. if location exist then initialize map
        if (config.locations) {
            if (config.country == 'KR') {
                naver_init_map(config.locations, 8, config.viewStore, config.markerImages, config.markerOnIcon);
            } else {
                multi_map_initialize(config.locations, 8, config.viewStore, config.markerImages, config.markerOnIcon);
            }
        }
        if (config.viewStore == 1) {
            pushmarker(0);
        }
        //filter for type
        $('.type-filter').on('click',function () {
            // making each type 0
            $('.type-filter-fields input').each(function () {
                $(this).val(0);
            });
            //making selected type 1
            $('#' +$(this).attr('data-filter')).val(1);
            $('#filter-form').submit();

        });
        $('#use-my-current-location').on('click', function () {
            navigator.geolocation.getCurrentPosition(function (position) {
                $('#current-lat').val(position.coords.latitude);
                $('#current-lng').val(position.coords.longitude);
                $('#current-location').val(1);
                //temporary comment, may be uncomment in future
                $('#filter-form').submit();
            });
        });

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
        });

        $('.map-open').on('click', function (event) {
            if ($(this).attr('state-dir')==0){
                $('.location-error').show();
                event.preventDefault();
            }
        });

        $( document ).ready(function() {
            if ($('#current-lat').val().length != 0) {
                $('.map-open').attr('state-dir','1');
            }
        });
        // for appending starting position with destination

        $('.map-open').each(function () {
            let link_with_destination = $(this).attr('href');
            let element = $(this);
            let longitude;
            let latitude;
            if (config.search){
                $(this).attr('state-dir','1');
                longitude = $("#search-lat").val();
                latitude  = $("#searcht-lng").val();
                setDirection(longitude, latitude, link_with_destination,element);
            } else {
                navigator.geolocation.getCurrentPosition(function (position) {
                    longitude = position.coords.longitude;
                    latitude  = position.coords.latitude;
                    setDirection(longitude, latitude, link_with_destination,element);
                });
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
            if (config.country == 'KR') {
                link_with_destination = url[0] + latitude+ ',' + longitude + '/' + url[1] + '/-/car';
            } else {
                link_with_destination = url[0]+longitude  + ',' + latitude + '&destination='+url[1];
            }
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
