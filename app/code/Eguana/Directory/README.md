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
- Zip/Postal Code is Autofilled, select each country where City/District and zipcode is autofilled.
- In the City is required for list, select each country where City/District is a required entry.
- Set the Allow to Choose City if It is Optional for Country field. (Dropdow will display for selecting city.)
- In Website Scope of VN and TW, default country, allowed country and top destination should be only each country.

#Important Notice

- This module was built on the policy that one website allow only one country. If multiple countries enabled as allowed on website scope, it will not work properly.
- To test address, remove all previous address data. All address data should be specific country based upon website scope. (In VN website, no TW customer address should exists.)


#Change Log
- April 27 2021 Vietnam Address Data added.


#Edit by Arslan
Add a before plugin (path = Eguana\Directory\Plugin\Model\Currency) in order to set the price's precision value equal to zero
for orders list and order's detail page at customer My Account(Dashboard).
And also from order list grid at the admin panel for vietnam site.
