/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 28/1/20
 * Time: 2:42 PM
 */
var map;
var markers = [];
var prevInfoWindow;
var prevMarker;
var prevMarkerIcon;

/**
 * google map initilazation
 * @param multi_location
 * @param map_zoom
 * @param isStore
 * @param markerImages
 * @param markerOnIcon
 */
function multi_map_initialize(multi_location, map_zoom, isStore, markerImages, markerOnIcon) {
    var locations = multi_location.split('||');
    var markerImages = markerImages.split('||');
    //TODO this has to be change with images. currently live kipling images are using
    var shopIcon = markerImages[0];
    var outletIcon = markerImages[1];
    var retailIcon = markerImages[2];
    var map, myLatLng, count, info, type, markerIcon, address, storeTitle, storeViewUrl, logo;
    for (count = 0; count < locations.length; count++) {
        info = locations[count].split('=>');
        myLatLng = new google.maps.LatLng(info[1], info[2]);
        storeTitle = info[0];
        type = info[3];
        address = info[4];
        storeViewUrl = info[5];
        logo = info[6];
        if (count === 0) {
            map = new google.maps.Map(document.getElementById('store_map_viewer'), {
                zoom: Number(map_zoom),
                center: myLatLng
            });
        }
        if (type == 'shop') {
            markerIcon = shopIcon;
        } else if (type == 'outlet') {
            markerIcon = outletIcon;
        } else if (type == 'retail') {
            markerIcon = retailIcon;
        }
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            icon: markerIcon,
            storeTitle : storeTitle,
            address : address,
            storeViewUrl : storeViewUrl,
            logo : logo
        });
        marker.addListener('click', (function (marker, count) {
            return function () {
                closeInfoWindowAndRevertMarker(prevInfoWindow, prevMarker, prevMarkerIcon);
                map.setZoom(15);
                map.setCenter(marker.getPosition());
                var infowindow = new google.maps.InfoWindow({
                    content: '<div class="description"><strong>' + marker.storeTitle + '</strong></div>'
                });
                infowindow.open(map, marker);
                prevMarkerIcon = marker.icon;
                marker.setIcon(markerOnIcon);
                prevInfoWindow = infowindow;
                prevMarker = marker;
                getStoreOfMarker(marker.storeTitle);
            };
        })(marker, count));
        markers.push(marker);
    }
}

/**
 * triger marker
 * @param id
 */
function pushmarker(id) {
    google.maps.event.trigger(markers[id], 'click');
}

/**
 * close wnidow
 * @param infoWindow
 * @param marker
 * @param markerIcon
 */
function closeInfoWindowAndRevertMarker(infoWindow, marker, markerIcon ) {
    if (infoWindow) {
        infoWindow.close();
    }
    if (marker) {
        marker.setIcon(markerIcon);
    }
}

/**
 * naver map initiliation
 * @param multi_location
 * @param map_zoom
 * @param isStore
 * @param markerImages
 * @param markerOnIcon
 */
function naver_init_map(multi_location, map_zoom, isStore, markerImages, markerOnIcon)
{
    var locations = multi_location.split('||');
    var markerImages = markerImages.split('||');
    //TODO this has to be change with images. currently live kipling images are using
    var shopIcon = markerImages[0];
    var outletIcon = markerImages[1];
    var retailIcon = markerImages[2];
    var map, myLatLng, count, info, type, markerIcon, address, storeTitle, storeViewUrl, logo;
    for (count = 0; count < locations.length; count++) {
        info = locations[count].split('=>');
        myLatLng = new naver.maps.LatLng(info[1], info[2]);
        storeTitle = info[0];
        type = info[3];
        address = info[4];
        storeViewUrl = info[5];
        logo = info[6];
        if (count === 0) {
            map = new naver.maps.Map(document.getElementById('store_map_viewer'), {
                useStyleMap : true,
                zoom: Number(map_zoom),
                center: myLatLng
            });
        }
        if (type == 'shop') {
            markerIcon = shopIcon;
        } else if (type == 'outlet') {
            markerIcon = outletIcon;
        } else if (type == 'retail') {
            markerIcon = retailIcon;
        } else if (type == 'dutyfree') {
            markerIcon = dutyfreeIcon;
        }
        var marker = new naver.maps.Marker({
            position: myLatLng,
            map: map,
            icon: markerIcon,
            storeTitle : storeTitle,
            address : address,
            storeViewUrl : storeViewUrl,
            logo : logo
        });
        marker.addListener('click', (function (marker, count) {
            return function () {
                closeInfoWindowAndRevertMarker_naver(prevInfoWindow, prevMarker, prevMarkerIcon);
                map.setZoom(15);
                map.setCenter(marker.getPosition());
                var infowindow = new naver.maps.InfoWindow({
                    content: '<div class="description"><strong>' + marker.storeTitle + '</strong></div>'
                });
                infowindow.open(map, marker);
                prevMarkerIcon = marker.icon;
                marker.setIcon(markerOnIcon);
                prevInfoWindow = infowindow;
                prevMarker = marker;
                getStoreOfMarker(marker.storeTitle);
            };
        })(marker, count));
        markers.push(marker);
    }
}

/**
 * push marker
 * @param id
 */
function pushmarker_naver(id) {
    var marker = markers[parseInt(id)];
    marker.trigger('click');
}

/**
 * open store box on click marker
 * @param title
 */
function getStoreOfMarker(title) {

    let count = 0;
    jQuery(".store-list").each(function() {
        let targetElementId = jQuery(this).attr("id");
        if (jQuery(this).attr("store-expand") == title + count) {
            jQuery(this).find('.store-secondary-info').slideDown();
            jQuery(this).addClass('selected-store');
            let targetElement = jQuery(this);
            scrollToTragetElememt("#"+targetElementId);
        } else {
            jQuery("#"+targetElementId).removeClass('selected-store');
            jQuery(this).find('.store-secondary-info').slideUp();
        }
        count ++;
    });
}

/**
 * close wind marker
 * @param infoWindow
 * @param marker
 * @param markerIcon
 */
function closeInfoWindowAndRevertMarker_naver(infoWindow, marker, markerIcon ){
    if (infoWindow) {
        infoWindow.close();
    }
    if (marker) {
        marker.setIcon(markerIcon);
    }
}

/**
 * scroll on store that wa click by map point
 * @param id
 */
function scrollToTragetElememt(id) {
    jQuery(id).get(0).scrollIntoView();
}
