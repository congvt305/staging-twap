define([
    'Magento_Customer/js/customer-data',
], function (customerData) {
    'user strict';

    var cacheKey = 'customer-ap-data';
    var sectionData = customerData.get(cacheKey);

    function notify(eventName) {
        // console.log('ap-customer AP_DATA_GCID ', window.AP_DATA_GCID);
        // console.log('ap-customer AP_DATA_CID ', window.AP_DATA_CID);
        // console.log('ap-customer AP_DATA_ISMEMBER ', window.AP_DATA_ISMEMBER);
        // console.log('ap-customer AP_DATA_ISLOGIN ', window.AP_DATA_ISLOGIN);
        // console.log('ap-customer AP_DATA_LOGINTYPE ', window.AP_DATA_LOGINTYPE);
        // console.log('ap-customer AP_DATA_CA ', window.AP_DATA_CA);
        // console.log('ap-customer AP_DATA_CD ', window.AP_DATA_CD);
        // console.log('ap-customer AP_DATA_CG ', window.AP_DATA_CG);
        // console.log('ap-customer AP_DATA_CT ', window.AP_DATA_CT);
        // console.log('ap-customer AP_DATA_CHANNEL ', window.AP_DATA_CHANNEL);
        // console.log('ap-customer push ', eventName);
        if (window.dataLayer) {
            window.dataLayer.push({'event': eventName});
        }
    }

    function isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    return function (config) {
        if (_.size( sectionData()) > 1) {
            window.AP_DATA_GCID = sectionData()['AP_DATA_GCID'];
            window.AP_DATA_CID = sectionData()['AP_DATA_CID'];
            window.AP_DATA_ISMEMBER = sectionData()['AP_DATA_ISMEMBER'];
            window.AP_DATA_ISLOGIN = sectionData()['AP_DATA_ISLOGIN'];
            window.AP_DATA_LOGINTYPE = sectionData()['AP_DATA_LOGINTYPE'];
            window.AP_DATA_CA = sectionData()['AP_DATA_CA'];
            window.AP_DATA_CD = sectionData()['AP_DATA_CD'];
            window.AP_DATA_CG = sectionData()['AP_DATA_CG'];
            window.AP_DATA_CT = sectionData()['AP_DATA_CT'];
        }
        window.AP_DATA_CHANNEL = isMobile() ? 'MOBILE' : 'PC';
        notify(config.eventName);
    };
});
