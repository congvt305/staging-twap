define([
    'ko',
    'underscore',
    'Magento_Ui/js/grid/listing',
    'jquery',
    'slick'
], function (ko, _, Listing, $) {
    $(document).ready(function() {
        $(".slick-slider").slick({
            dots: true,
            infinite: true,
            autoplay: true,
            responsive: [
                {
                    breakpoint: 9999,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                }
            ],
        });
    });
});
