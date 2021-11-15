/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 27/8/20
 * Time: 7:24 PM
 */
var config = {
    'config': {
        'mixins': {
            /*'Magento_Wishlist/js/add-to-wishlist': {
                'Eguana_CustomCatalog/js/add-to-wishlist-mixins': true
            },*/
            "Magento_Swatches/js/swatch-renderer" : {
                "Eguana_CustomCatalog/js/swatch-renderer-mixin": true

            },
            'Magento_Catalog/js/price-box': {
                'Eguana_CustomCatalog/js/price-box': true
            }
        }
    }
};
