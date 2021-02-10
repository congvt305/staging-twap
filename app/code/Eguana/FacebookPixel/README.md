# Facebook Pixel 

`Website` : Main Website URL

`Author` : Ali Yaqoob  

####Description:
Facebook Pixel extension allows you to track your visitors actions by sending events to your Facebook Ads Manager and the Facebook Analytics dashboard.

####Key Features:
- Page view:
    - Content view
    - All and every page load 
    

- Product page:
    - Add to cart event

    
- Checkout page:
    - Initiate checkout event 

    
- Success page
    - Purchase event
    
### Module Installation

```
1.  php bin/magento Module:enable Eguana_FacebookPixel  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management
```

### General Configuration

Navigate to **Stores ⇾ Configuration**
   
![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to **EGUANA EXTENSION TAB ⇾ Facebook Pixel** in the left panel.

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/90a0e6ebe5951d89e0e9699fe73de5c1.png)

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/4f9ed7122c65dfa34e167b8e2f2562e9.png)

Add configuration values in the following fields and click the Save Config button.
#### General Configuration
![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b15c635cbf4374dce1d9208d71cd8d54.png)

#####(1) Enable or Disable module
Admin can enable or disable module from Enable feature.

#####(2) Pixel ID
Admin fill the Facebook Pixel Track Code ID

#####(3) Tax Include Settings
Admin can select option if want to Include Product Taxes.

#####(4) Track Options
Admin can select which events want to track.

- Product page Track event 


- Checkout page Track event


- Success page track event

#####(5) Note
This is website base module

### Facebook Pixel Chrome Extension:

Add Extension in chrome to check Facebook Pixel working 

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/6881d454e62d3d689c31bf785cd4d12b.png)

###Website page Extension

![image](https://i.ibb.co/sjbcrvq/screenshot-l-dev-54ta5gq-kqevkj6gpg7si-ap-3-magentosite-cloud-2021-02-08-11-12-35.png)

###Product page Track event

![image](https://nimbus-screenshots.s3.amazonaws.com/s/40aaaec6267a499f9779b7f4ae768afa.png)

###Checkout page Track event

![image](https://nimbus-screenshots.s3.amazonaws.com/s/9a966bd4fd505114a322e52cabcdd4fe.png)

###Success page track event

![image](https://nimbus-screenshots.s3.amazonaws.com/s/a14de32bbd15861d84b4c8db242f06ce.png)


