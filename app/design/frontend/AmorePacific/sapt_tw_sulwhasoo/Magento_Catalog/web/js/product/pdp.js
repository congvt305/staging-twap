
require(['jquery', 'mage/gallery/gallery','loader'], function($, gallery){
    $('[data-gallery-role=gallery-placeholder]').on('gallery:loaded', function () {
        $('.product-info-wrapper').css('visibility', 'unset');
    });
});
