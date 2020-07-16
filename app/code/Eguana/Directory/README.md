# Eguana Directory

`Website` : Main Website URL 
`Author` : Sonia  
`DB Table Name` : eguana_directory_region_city
`EAV Attribute` : city_id for Customer Address Entity


####Description:

The Eguana_CustomerRefund module provides enhanced local features for Taiwan addition to Magento_Directory Module which enables the management of countries and regions recognized by the store and associated data
                                                                                                                        like the country code and currency rates. Also, enables conversion of prices to a specified currency format.

####Key features:
 
- Install TW region and city data in the database
- Customer Address Form Field Handling For City and Zipcode
- Load optional (dropdown) cities.
- Load Zipcode automatically
- Handle Currency Fix to remove decimal point.
 
#Module Installation  

```
1.  php bin/magento Module:enable Eguana_Directory
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile
```

#General Configurations

On the Admin sidebar, go to Stores > Settings > Configuration.

Under General in the left panel, choose General.

Expand Expansion selector the City Options section and do the following: 

- In the City is required for list, select each country where City/District is a required entry.
- Set the Allow to Choose City if It is Optional for Country field. (Dropdow will display for selecting city.)



