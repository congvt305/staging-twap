Eguana_Pip

Website: Eguana

Author: Muhammad Umer Farooq

DB Table Name : 
 
Explanation: This module will allow customer to leave account and become secession customer. Customer perform secession in my account section willingly. 

# Pip

Description:

PIP is the abbreviation for Personal Information Protection. Egauna Pip allows you to manage the consent information and provide the easy approach to manage all the users. It allows the user to access their personal information usage and perform secession.

Requirements:

    - User should be able to leave account and perform seccession.

Key features:

      1. Admin can enable disable whole module

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_Pip

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Personal Information Protection** under Eguana tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Personal Information Protection** under Eguana tab in the left panel.

**General Setting**

* Enable Module

This is module main enable/disable button. This will decide either module is enable or disabled

**Frontend**

After enabling module.

You can see "Click here to leave account" on Customer My Account page.

If user clicks this button, Then user information will be encrypted and user can not login again.

Following information of customer will be encrypted.

Customer First Name, Middle Name, Last Name, Dob, Mobile Number, Phone, Billing Address Information, Shipping Address Information, 

Order, Invoice , Credit memos, Shipments, RMA, Newsletter Subscription, Customer Reviews, Customer Address, Invitations.
