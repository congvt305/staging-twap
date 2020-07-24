# Eguana_GWLogistics

 - Website: Amore Taiwan
 - Author: Sonia Park
 - DB Table Name : 
     - eguana_gwlogistics_quote_cvs_location, 
     - sales_order_address : cvs_location_id
     - quote_address : cvs_location_id
     - sales_shipment : all_pay_logistics_id
     - magento_rma_shipping_label : rtn_merchant_trade_no
 
####Description:

Shipping module that Integrates with Green World Logistics Convenient Store Pickup.

####Key features:
 
 - Save customer's cvs selection in the database.
   customers can select cvs location from a map during the checkout shipping method select step.
 - Automatically creates shipment after invoice is created and paid.
   After creating an order, a shipment is created automatically.
 - Call GW API to create a logistic order.
   During the shipment creation, the module will call API to create a logistics order and query tracking number.
 - Save Track data in the database and display in the frontend and backend
   When the module received the response from the API, it saves the track in the database and display it both in the frontned and the backend.
 - Handles RMA shipment for backend
   Admin user can create a reverse logistics order by clicking a button in the backend Sales > Rma > View.
 - Handles customer notification via sms message for Return Code
   When the module receives the Return Code for the RMA, module will notify the customer by sending a SMS message.
 - Handles RMA shipment for frontend
   The module displays the return code in the frontend My Account > My Returns > View Detail.
 - Handles GW notification status.
   When Green world sends the status notification, module will save the notification and display the in the frontend and backend.
 - Handles Transaction Log
   When tracking data is requested by a customer or admin user, the module will display notification status history.
 
#Module Installation  

```
1.  php bin/magento Module:enable Eguana_GWLogistics
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile
```

#General Configurations

On the Admin sidebar, go to Stores > Settings > Configuration.

Sales > Shipping Methods in the left panel, choose Green World Logistics.

 - Enable/Disable the module.
 - Configure Merchant ID, Hash Key, Hash IV.
 - Select Developer/Production Mode.
 - Enable/Disable SMS notification for Return code.



