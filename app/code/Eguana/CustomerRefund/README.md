# Eguana Customer Refund

`Website` : Main Website URL 
`Author` : Sonia  
`DB Table Name` : eguana_customerrefund_bankinfo  

####Description:

The Eguana_CustomerRefund module handles customer's refund/refund to request action in the frontend.(Native Magento does not supprot it.)

####Key features:
 
 - Customer can refund order online when the order is paid with credit card. (My Account > My order > View)
 - Customer can request to offline refund when the order is paid with webATM. (My Account > My order > View)
 - When customer requests to offline refund, customer can save bank information in the system, and admin user can view the information in Customer Bank Information Tab of Sales > Order > View page. When the bank information is stored in the system, it should be encrypt with AES256.
 - To handle the request, admin user first should unhold the order and create a credit memo with offline refund. 
 
#Module Installation  

```
1.  php bin/magento Module:enable Eguana_GWLogistics
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile
```

#General Configurations

In Admin Panel, Navigate to **Stores­ ⇾ Configuration**

Navigate to **EGUANA EXTENSION ⇾ Customer Refund** in the left panel.

(1)Clicking general tab will show module's Enable/Disable configuration. 

