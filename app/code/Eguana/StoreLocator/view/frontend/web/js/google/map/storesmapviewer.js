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
 * @param mapNorth
 * @param mapSouth
 * @param mapWest
 * @param mapEast
 */
function multi_map_initialize(multi_location, map_zoom, isStore, markerImages, mapNorth, mapSouth, mapWest, mapEast) {

    let locations = multi_location.split('||');
    let markerImagesObj = JSON.parse(markerImages);
    var map, myLatLng, count, info, markerIcon, address, storeTitle, storeViewUrl, storeType, telephone;
    const COUNTRY_BOUNDS = {
        north: mapNorth,
        south: mapSouth,
        west: mapWest,
        east: mapEast
    };

    for (count = 0; count < locations.length; count++) {
        info = locations[count].split('=>');
        myLatLng = new google.maps.LatLng(info[1], info[2]);
        storeTitle = info[0];
        address = info[3];
        storeViewUrl = info[5];
        storeType   = info[4];
        telephone = info[6];
        if (storeType == 'RS') {
            markerIcon = markerImagesObj.RS_1.image;
        }

        if (storeType == 'FS') {
            markerIcon = markerImagesObj.FS_1.image;
        }
        if (count === 0) {
            if (window.AP_DATA_COUNTRY === 'tw'){
                map = new google.maps.Map(document.getElementById('store_map_viewer'), {
                    zoom: Number(map_zoom),
                    minZoom:13,
                    center: myLatLng,
                    restriction: {
                        latLngBounds: COUNTRY_BOUNDS,
                        strictBounds: false,
                    }
                });
            } else {
                map = new google.maps.Map(document.getElementById('store_map_viewer'), {
                    zoom: Number(map_zoom),
                    center: myLatLng,
                    restriction: {
                        latLngBounds: COUNTRY_BOUNDS,
                        strictBounds: false,
                    }
                });

            }
        }
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            icon: markerIcon,
            storeTitle : storeTitle,
            address : address,
            storeViewUrl : storeViewUrl,
            storeType   :   storeType,
            telephone   : telephone
        });
        marker.addListener('click', (function (marker, count) {
            return function () {
                closeInfoWindowAndRevertMarker(prevInfoWindow, prevMarker, prevMarkerIcon);
                map.setZoom(15);
                map.setCenter(marker.getPosition());
                var infowindow = new google.maps.InfoWindow({
                    content: '<div class="description"><h3>' + marker.storeTitle +
                        '</h3><p>' + marker.address + '</p>' +
                        '<p>' + marker.telephone + '</p>' +
                        '</div>'
                });
                infowindow.open(map, marker);
                prevMarkerIcon = marker.icon;
                if (marker.storeType == 'RS') {
                    markerIcon = markerImagesObj.RS.image;
                    prevMarkerIcon = markerImagesObj.RS_1.image;
                }
                if (marker.storeType == 'FS') {
                    markerIcon = markerImagesObj.FS.image;
                    prevMarkerIcon = markerImagesObj.FS_1.image;
                }
                marker.setIcon(markerIcon);
                prevInfoWindow = infowindow;
                prevMarker = marker;
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
 * open store box on click marker
 * @param title
 */
function getStoreOfMarker(title) {

    let count = 0;
    $(".store-list").each(function() {
        let targetElementId = 'store_map_viewer';
        if ($(this).attr("store-expand") == title + count) {
            $(this).find('.store-secondary-info').slideDown();
            $(this).addClass('selected-store');
            scrollToTragetElememt("#"+targetElementId);
        } else {
            $("#"+targetElementId).removeClass('selected-store');
            $(this).find('.store-secondary-info').slideUp();
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
    $(id).get(0).scrollIntoView();
}
