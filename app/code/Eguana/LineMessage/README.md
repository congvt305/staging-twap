Eguana_LineMessage

Website: Amore

Author: Muhammad Umer Farooq

Explanation: This module will send message to customer from Dot Digital Platform using LINE Message API

# LINEMessage

Description:

Register module will be mainly used to send message to customer from Dot Digital Platform using LINE Message API.

Requirements:

    - User should be able select Line message agreement ( this will decide if user want to reeieve line message or not )
    - Admin should be able to map Customer Line Id with Dot Digital Line Id Attribute
    - Admin should be able to change Line Message Agreement Text

Key features:

      1. Admin can enable or disable LineMessage
      2. Admin can change line agreement text
       
Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_LineMessage

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration ⇾ EGUANA EXTENSIONS ⇾ Social Login.

**Configuration**

1. Navigate to **Stores ⇾ Configuration ⇾ EGUANA EXTENSIONS ⇾ Social Login.

 ![Configuration](https://i.ibb.co/tM9qH2r/1.png)
 
* Line Messages Agreement Text

Here admin can change the line message agreement text

Navigate to **Stores ⇾ Configuration ⇾ EGUANA EXTENSIONS ⇾ Line Message.

 ![Configuration](https://i.ibb.co/Fgh533r/2.png)

* General Setting
* Enable Module

Here you can enable or disable whole module

* LINE
* Enable

Here you can enable or disable line message

* Channel ID

Here you can enter channel id

* Channel Secret

Here you can enter channel secret

* Channel Access Token

Here you can enter channel access token

**Frontend**

Customer can select line message agreement if he want to receive line message or not when signing up with LINE

 ![linemessage](https://i.ibb.co/DQJhg1V/3.png)
 
 ![linemessage](https://i.ibb.co/bKfdHFm/6.png)

After enabling LINE Message module.

You can add any customer in to the channel by scanning QR Code in below links for different stores

Sulwhasoo Add Friend LINK :
https://lin.ee/Jp8BnvL

laneige add friend LINK:
https://lin.ee/Ele6fI9

We can call the rest api to send message to user by giving customer email, and message

 ![linemessage](https://i.ibb.co/2ddSrNd/5.png)

