Eguana_Theme v1.0 

Website : Main Website 
Author : Hyuna
Explanation : Magento Design Theme Module
Core Source Rewrite, bxSlider js import, rwdImageMaps js import, swiper4.5 js import
Core Source Rewrite List
 - Magento_Base : Product Gallery Image Fotorama js Override (Image size : 700*700)
 
 

 Ammore-Theme Upgrade to Magento2.3.4
 
 Remove the dependency of the following modules
 - Share
 - EcommerceStatus
 - CustomCheckout
 
 The first module (share) was required to share product on social media.
 
 The second module (EcommerceStatus) was required to enable the ecommerce functionality.
 
 The Third module (CustomCheckout) was used to add the placeholder in the form when placing an order.
 
 for first 2 modules I use Exception to check if module is enable or not,
 and for the third module I add the CustomCheckout's plugin in the default Theme module,
 now there is only one module.

 For Taiwan language pack the translantion is in progress by Magento 1500 lines or words are translated out of 16400 lines and words 