Eguana_CustomCatalog v1.0 

Website : Main Website 
Author : Soojin
Explanation : Add default attribute value to product link for Tagging

###Edit by Arslan
Add After plugin for validate method to remove the extra error message in (error shown in image)

`Path: Eguana\CustomCatalog\Plugin\Model\Quote\Item\QuantityValidator`

![remove-message](https://nimbus-screenshots.s3.amazonaws.com/s/21ef9af692abf97216e63da4d2714dcd.png)

###Edit by Umer
Add before Plugin Magento\ConfigurableProduct\Helper\Data 
`Path: Eguana\CustomCatalog\Plugin\Helper\Data`
Show out of stock product prices for configurable product (child products)

![remove-message](https://i.ibb.co/cYy0X2g/configurable.png)

When any of the child product is out of stock then whole bundle product becomes out of stock.

Before ( all childs are in stock )
![remove-message](https://i.ibb.co/PcFBmPQ/bundleimg1.png)

After ( out of stock childs )
![remove-message](https://i.ibb.co/6tFXFdQ/bundleimg2.png)

Changed Files Path
Eguana\CustomCatalog\ViewModel\DisableAddToCart
app/design/frontend/AmorePacific/laneige/Magento_Catalog/templates/product/view/addtocart.phtml
