var config = {
    map: {
        '*': {

            addAjaxWishlist:  'Sapt_AjaxWishlist/js/ajax-wishlist',
            wishlistAction:  'Sapt_AjaxWishlist/js/wishlist-actions',

        }
    },
    // Fix off click wish list
    shim: {
        'Sapt_AjaxWishlist/js/wishlist-actions': ['Sapt_Ajaxcart/js/ajax']
    },
};
