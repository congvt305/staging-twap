# SATP Megamenu extension

`Website` : HK shlwhasoo   

####Description:

This module allows to manage mega menu

####Key features:
 
 - Configuration megamenu for each category
 - Add cms content to the main menu.
 
#Categories Attribute
`satp_menu_cat_columns` : dropdown select how many sub category column will be shown

`satp_menu_float_type` : dropdown select to show category on left/right side

`satp_menu_block_right_width` : dropdown select to set width of the right content

`satp_menu_block_right_content` : page builder content to add the right content
 
#Module Installation  

```
1.  php bin/magento Module:enable Satp_Megamenu
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile
4.  php bin/magento setup:static-content:deploy
```

#General Configurations

In Admin Panel, Navigate to **Stores­ ⇾ Configuration**

Navigate to **SATP ⇾ Megamenu** in the left panel.

(1)Clicking general settings tab will show module's Enable/Disable configuration.

(2)Clicking Custom Links & Block tab to setting cms content for main menu. 

#Category Configurations
In Admin Panel, Navigate to **Catalog­ ⇾ Categories**

(1)Select any category then click to Satp Menu tab:

- Sub Category Columns (default `1 column`)
- Float (default `left`)
- Right Block Width (default `Do not show`)
- Right Block


