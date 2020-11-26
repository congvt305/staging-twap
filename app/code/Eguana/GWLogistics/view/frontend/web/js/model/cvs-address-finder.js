define([
    'underscore',
    'jquery',
    'uiRegistry'
], function (
    _,
    $,
    registry
) {
    'use strict';

    var checkoutProvider = registry.get('checkoutProvider'),
        addressString = '',
        getReplacedCity = function (region, addressStr) {
            var cityExceptions = [
            {
                'regionName': '新竹市',
                'cityName': '北區',
                'replacement': '新竹市'
            },
            {
                'regionName': '新竹市',
                'cityName': '東區',
                'replacement': '新竹市'
            },
            {
                'regionName': '新竹市',
                'cityName': '香山區',
                'replacement': '新竹市'
            },
            {
                'regionName': '臺中市',
                'cityName': '中區',
                'replacement': '中 區'
            },
            {
                'regionName': '臺中市',
                'cityName': '東區',
                'replacement': '東 區'
            },
            {
                'regionName': '臺中市',
                'cityName': '南區',
                'replacement': '南 區'
            },
            {
                'regionName': '臺中市', //
                'cityName': '西區',
                'replacement': '西 區'
            },
            {
                'regionName': '臺中市',
                'cityName': '北區',
                'replacement': '北 區'
            },
            {
                'regionName': '彰化縣',
                'cityName': '員林市',
                'replacement': '員林鎮'
            },
            {
                'regionName': '雲林縣',
                'cityName': '口湖鄉',
                'replacement': '口湖區'
            },
            {
                'regionName': '雲林縣',
                'cityName': '元長鄉',
                'replacement': '元長區'
            },
            {
                'regionName': '雲林縣',
                'cityName': '水林鄉',
                'replacement': '水林區'
            },
            {
                'regionName': '雲林縣',
                'cityName': '北港鎮',
                'replacement': '北港區'
            },
            {
                'regionName': '雲林縣',
                'cityName': '四湖鄉',
                'replacement': '四湖區'
            },
            {
                'regionName': '雲林縣',
                'cityName': '台西鄉',
                'replacement': '臺西鄉'
            },
            {
                'regionName': '嘉義市',
                'cityName': '西區',
                'replacement': '嘉義市'
            },
            {
                'regionName': '嘉義市',
                'cityName': '東區',
                'replacement': '嘉義市'
            },
            {
                'regionName': '臺南市',
                'cityName': '中西區',
                'replacement': '中西 區'
            },
            {
                'regionName': '臺南市',
                'cityName': '東區',
                'replacement': '東 區'
            },
            {
                'regionName': '臺南市',
                'cityName': '南區',
                'replacement': '南 區'
            },
            {
                'regionName': '臺南市',
                'cityName': '北區',
                'replacement': '北 區'
            },
            {
                'regionName': '屏東縣',
                'cityName': '鹽埔鄉',
                'replacement': '盬埔鄉'
            },
            {
                'regionName': '臺東縣',
                'cityName': '台東市',
                'replacement': '臺東市'
            }
            ];

            return  _.find(cityExceptions, function (cityException) {
                var pattern = new RegExp(cityException.cityName);
                return cityException.regionName === region.title && pattern.test(addressStr);
            });
        };

    return {
        getAddressDataFromString: function (cvsAddressString) {
            var addressData = {},
                regionString, region, regionId, cities, city, street, country_id;

            if (!cvsAddressString) {
                return {};
            }
            addressString = cvsAddressString;
            regionString = cvsAddressString.substr(0,3);

            region = this.findRegionByName(regionString)
            regionId = region.value;
            cities = this.getCitiesByRegionId(regionId);
            city = this.getCityFromString(region, cities);

            addressData.region = regionString;
            addressData.regionId = regionId;
            addressData.city = this.getCityName(city);
            addressData.city_id = city.value;
            street = addressString.replace(regionString, '');
            street = street.replace(addressData.city, '');
            addressData.street = street;
            addressData.postcode = this.getPostcode(city);
            addressData.countryId = region.country_id;

            return addressData;
        },

        getCitiesByRegionId: function (regionId) {
            if (!addressString) {
                return {};
            }
            return  _.filter(checkoutProvider.get('dictionaries').city_id, function (city) {
                return city.region_id === regionId;
            });
        },

        findRegionByName: function (regionString) {
            var regions = checkoutProvider.get('dictionaries').region_id;
            if (/^台.*$/.test(regionString)) {
                regionString = regionString.replace('台', '臺');
            }
            return _.find(regions, function (region) {
                return region.title === regionString;
            });
        },

        getCityFromString: function (region, cities) {
            var addressStr, regionStr, found, cityReplaced;

            regionStr = region.title;
            regionStr = regionStr.replace('臺', '台');
            addressStr = addressString.replace(regionStr, '');

            found =  _.find(cities, function (city) {
                var pattern = new RegExp(city.title);
                return pattern.test(addressStr);
            });

            if (!found) {
                found =  _.find(cities, function (city) {
                    var pattern = new RegExp(city.title);
                    cityReplaced = getReplacedCity(region, addressStr);
                    return pattern.test(cityReplaced.replacement);
                });
            }
            // console.log('found', found);
            return found;
        },

        getCityName: function (city) {
            return city.title ? city.title : 'XXXX';
        },

        getPostcode: function (city) {
            return city.code ? city.code : '0000';
        },
    };
});