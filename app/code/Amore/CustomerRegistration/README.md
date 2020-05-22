Amore_Customer v2.0.0

Website: Amore

Author: Abbas Ali Butt

DB Table Name :
 
Explanation: This module will customize the registration process according to the Amore requirements

# Customer

Description:

Register module will be mainly used to customize the user registration process, call API of POS to get the offline
 registered user details

Requirements:

    - Check whether customer already exist in POS or not
    - If exist in POS then fetch the infromation from POS and load into the form for registration in Magento
    - If not exist in POS then load the form without any existing infromation. But load the phone number from step 1 and
      in step 2 customer is not allowed to change the phone number
    - When existing customer registration completed also update in the POS with the new infromation.  
    - When new customer registration completed also create a new customer in the POS
    - While registration also check whether phone number already exist or not. If already exist in Magento then show 
      CS information page.


Key features:

      1. As a store representative I want to scan a QR code so that I can register the customer.
      2. As an old user I want to add my POS information so that my old information will be fetched to Magento from POS
       system
      3. As a new customer I want to register without POS information so that I can register as a new customer
      4. As a customer I want to provide my newsletter direct mail address so that Amore can send me news letters on
       that address
      5.Show the SMS verification relateld fields to the customer
      6 Show the timer to expire the code
      7. As an admin I want to see from which store user has been registered so that I can list users register from
       specific store

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_CustomerRegistration

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Curstomer Registration** under Amore tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Curstomer Registration** under Amore tab in the left panel.