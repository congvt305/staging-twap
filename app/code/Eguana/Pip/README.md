Eguana_Pip

Website: Eguana

Author: Muhammad Umer Farooq

DB Table Name : 
 
Explanation: This module will allow customer to leave account and become secession customer. Customer perform secession in my account section willingly. 

# Pip

Description:

PIP is the abbreviation for Personal Information Protection. Egauna Pip allows you to manage the consent information and provide the easy approach to manage all the users. It allows the user to access their personal information usage and perform secession.
It allows the admin to view the list of terminated customers in the Terminated Customers Grid.

Requirements:

    - User should be able to leave account and perform seccession.

Key features:

      1. Admin can enable disable whole module
      2. Admin can view the list of Terminated Customers

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

By clicking the button, it will delete customer account.

**Admin Panel Terminated Customers Grid**

Admin can view the list by nevigate to 
1. Navigate to **Customers** and click on **Terminated Customers**

![terminated-customers](https://nimbus-screenshots.s3.amazonaws.com/s/0bfc338a493306104f596f6d5a9ad14a.png)

2. Admin can view the grid of the terminated customers grid.

![terminated-customers-grid](https://nimbus-screenshots.s3.amazonaws.com/s/a25cf125b40b911e2a27d9deef88f033.png)

## 1) Search by keyword

Search by keyword is used to search specific keywords (for integration number and IP address) available list of record. Just write the keyword (for integration number and IP address) in input field and press the enter button. 

## 2) Filters

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. Also you can filter data according to the Customer ID, Integration No and IP Address.
Add the parameters and click Apply Filters Button.

## 3) Export

Export option is used to export the terminated customers list in CSV file.
