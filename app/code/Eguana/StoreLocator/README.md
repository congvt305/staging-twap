Eguana_StoreLocator v2.0.1

Website: Amore

Author: Muhammad Umer Farooq

DB Table Name : storeinfo
 
Explanation: This module will allow the customer to look on the google map physical location of amore store across the country

# Store Locator

#####Description :

Eguna Store Locator allows you to manage the physical stores. It allows the admin to add physical store location with timings of everyday work time . Admin has to put the exact location (Longitude and Latitude).


Requirements:

    - Admin can enable disable the feature based on country
    - It should able to support multiple countries


####Key features :

- Admin can add stores with directions and in each state.
- Admin can set the everyday work time of each store.
- User can view all stores on frontend with map view.

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_StoreLocator

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Store Locator** under Eguana tab.


**Configuration**

How to enable store Locator:

            Stores -> configuration -> Eguana Extensions -> Eguana Store Locator

 ![](https://i.ibb.co/3hJVymr/store2.png)
 
 ![](https://i.ibb.co/gFs3477/store1.png)
 
 ###General Configurations :
 
 -  Module Enable
 
 This is module main enable/disable button. This will decide either module is enable or disabled

-  Google Map Api key
 
 Google Map Api key is required to use the services of google map which will use on both frontend and backend.

-  Store Locator Bottom Block Id
 
Here enter the cms block id to show custom content at the bottom of store locator page

###Front Stores Map :

- Map height

This will how much height will be set on frontend for map viewer. By default its value is 420px.

- Map Zoom

This will set the zoom parameter while initializing the map. By default its value is 19.

###Front Stores Map :

- Map height

This will how much height will be set on frontend for map viewer. By default its value is 420px.

- Map Zoom

This will set the zoom parameter while initializing the map. By default its value is 19.


# 1. Functionalities for admin :

Admin can manage stores from admin panel which will be shown on frontend.

1.1 Manage Stores

In Manage Store Admin can manage all stores. Admin can see all the stores with store views.

                     Stores -> Store Locator -> Manage Stores


 ![](https://i.ibb.co/HnrGYs8/menu.png)
 
 ![](https://i.ibb.co/BNg1vBv/store3.png)

 
#####1.2 Add New Store

In Manage Store Admin can add new store.

- In this custom module now admin can add country for each each store and this country filter will be used on frontend

- In this custom module now admin can add add store type for each store.
 Select type Flagship Store and Roadshop store
 
- Customer can add store title, location,  address, telephone, email and store timmings.
 
 - Click on Save to save the store.
 
 ![](https://i.ibb.co/XjFmTZH/screencapture-chrome-extension-fdpohaocaechififmbbbbbknoalclacl-editor-html-2020-06-30-20-37-21.png)

 **Frontend**
 
 - Go to store list page to view all the stores 
 
 - Search stores by store name and address
 
 - All stores are displayed based on their type RS or FS
 
 - There are separate marker for both store types blue and red
 
  ![](https://i.ibb.co/gmrmjh5/storefront.png)

  ![](https://i.ibb.co/GTgfK9Q/storefornt2.png)

## Latitude Longitude

The latitude and longitude of a country can be found from: https://gist.github.com/graydon/11198540

### 1) Taiwan
- north: 25.2954588893
- south: 21.9705713974
- west: 120.106188593
- east: 121.951243931

### 1) Vietnam
- north: 23.3520633001
- south: 8.59975962975
- west: 102.170435826
- east: 109.33526981
