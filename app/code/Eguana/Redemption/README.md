# Eguana Redemption

`Website`       : Main Website URL 
`Author`        : Arslan  
`DB Table Name` : - eguana_redemption
                  - eguana_redemption_store
                  - eguana_redemption_user
                  - eguana_redemption_counter  
`Front Page URL`: Redemption page will be available for customer when redemption URL send by email and SMS 
                  once module is activated'

####Description: Redemption Module

A new module for redemption users to register to redemptions in available stores.

####Key features:

- Admin can Enable or Disable Redemption Module, To either show an redemption on front end or not to show.
- Admin can add Redemption Title, Redemption Thumbnail, Redemption Start and End, Description, Precautions and SEO Data.
- Admin can Select store view and select store from the list of counter which shows the available store for redemption
- Admin can design the Description content and Precautions content using page builder.
- All the Redemptions's Title, thumbnail image, Description content, Precautions content added by admin can view on the frontend listing page.
- Admin can send the frontend link to the customer which shows the registration form with the redemption title, description, precautions and form field where customer can register for redemption
- When customer register for the redemption he will receive an email and sms for confirmation link
- if a user not recieve an email or sms he can resend the email and sms by clicking resend button after counter time which will be set in admin panel configuration

###Note:

1) Email is only sending when first time user register for the redemption by submitting the redemption form.(email is not sending when admin changes any status from the admin panel).

2) When user register for the redemption by submiting the redemption form and when resend email/sms button and timer appears all the form fields are readonly with user data. The reason is that user can resend email/sms to that email address and phone number which user add during the form filling. However if these field are not readonly and user change email address or phone number the email/sms not send to the new email address/phone number, in this scenario user have to register with new email address and phone number by refresh the page.

#Module Installation  
```
1.  php bin/magento Module:enable Eguana_Redemption  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management
```

 **General Configurations**

Navigate to **Stores ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION ⇾ Redemption Configuration in the left panel.

![redemption-config](https://nimbus-screenshots.s3.amazonaws.com/s/7e91d6b14374a444ff3363e352b2aae2.png)

Add configuration Values in the following fields and click the save button.

![config-fields](https://nimbus-screenshots.s3.amazonaws.com/s/696e52f9dd26de5b60132d846163d06a.png)

![time-interval](https://nimbus-screenshots.s3.amazonaws.com/s/a44f9f76bbd635077c24536c111d7e96.png)

![number-validation](https://nimbus-screenshots.s3.amazonaws.com/s/7c62754a5151cd62accbf46e1fc86bc3.png)

#####(1) Enable or Disable Module

This is Module enable/disable button. This will decide either Module is enable to show on front end or if disabled it will not show on the front end.

#####(2) Email Sender

This is to select the email sender which use there name and email address when customer recieve email after registration.

#####(3) Send email to customer (yes/no)

This field is used to select to enable or disable email functionality to send email when customer register.

#####(4) Automatically Registration

This field is used to select the email template when customer register.

#####(5) Send SMS To Customer Automatically (yes/no)

This field is used to select to enable or disable sms functionality to send sms when customer register.

#####(6) Registration

This field is used to select the sms template when customer register.

#####(7) Google Recaptcha

This field is to enable or disable the google recaptcha at frontend registration form when customer register for registration.
And this configuration is website base

#####(8) Resend Email And Sms Interval

This field is to set the interval time which is used to enable the resend button. The button after submit the form will disable and resend button will be enable after this time.

#####(9) Minimum Mobile Number Digits

This field is to set the minimum mobile number digits which validate the user to enter the valid phone number digits when user register for redemption by submitting the form.

#####(10) Maximum Mobile Number Digits

This field is to set the maximum mobile number digits which validate the user to enter the valid phone number digits when user register for redemption by submitting the form.

#####(11) Now Click on Save Config button to save the configuration.

![save-button](https://nimbus-screenshots.s3.amazonaws.com/s/65cf2392bf4a99ff4a96c47066357601.png)

#  Manage Redemption
 
Go to CONTENT > Manage Store Contents and click on Redemption

![manage-evebr](https://nimbus-screenshots.s3.amazonaws.com/s/b5d924fe5ddaada350619a9154ef49f9.png)

It will open a Manage Redemption Grid. In the grid all records will be shown which admin add by admin panel.

Example image of an admin grid is below.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/fbe6cccf2ee75e64e648e8a1aae405e5.png)

 **Explore The Grid**
 
 ## 1) Add New Redemption
 
 Click on Add New Redemption Button.
 
 ![add-new](https://nimbus-screenshots.s3.amazonaws.com/s/dbc7ad716cab7f108568608a3a10e67f.png)
 
It will open a form to Add New Redemption.

Below is the example image of the add new form.

Form Part 1 shows form fields

![add-nw-form](https://nimbus-screenshots.s3.amazonaws.com/s/2ac5611c182b76ba9c95aa1b5def3167.png)

Form Part 2 shows content area 

![add-content-area](https://nimbus-screenshots.s3.amazonaws.com/s/d11a6829b077d9efb452e41211895d87.png)

Form Part 3 shows Search Engine Optimization

![add-seo-area](https://nimbus-screenshots.s3.amazonaws.com/s/c6096a86c792e6cb255325871466684c.png)

Form Part 4 shows the list of registered customer in current redemption

![registered-customer--area](https://nimbus-screenshots.s3.amazonaws.com/s/fef31f28fe8eed38ddb3e1cf92d5f712.png)

Explore the every field of add new form in details.

![three-fields](https://nimbus-screenshots.s3.amazonaws.com/s/78a216ba51f0a2e3145d30513901a05e.png)

#####(1) Enable Redemption

This is Redemption main enable/disable button. This will decide either Redemption is enable to show on front end or if disabled it will not show on the front end.

#####(2) Redemption Title

This is Redemption main title. Add Redemption name or title here.
 
#####(3) Thumbnail Redemption
 
This field is used to add the thumbnail image which will show on listing page. Click on Upload and select an image which will show in the frontend page.

#####(4) Store View
    
This is Redemption store view. This will decide where Redemption information will shown.
    
#####(5) Counter Name

This is store counter name in which allowed for redemption store list is showing select one or more stores where the redemption will held

#####(6) Total Quantity

This field is to defined how many total number of users can registered for the current redemption

#####(7) CMS Block

This drop down shows the list of the cms blocks select one cms block to show at the top of the frontend registration page

#####(8) Start Date

Click on the calender and it will open a pop-up called date picker as shown in the image below.

![calender](https://nimbus-screenshots.s3.amazonaws.com/s/058ebced5c4d00e126f6c5cc0773c583.png)

and from that date picker select the redemption start date.

#####(9) End Date

Click on the calender and it will open a pop-up called date picker as shown in the image below.

![calender](https://nimbus-screenshots.s3.amazonaws.com/s/058ebced5c4d00e126f6c5cc0773c583.png)
    
    
Form Part 2 details

This is the page builder section where admin can design the description and precautions area for frontend

![content-section](https://nimbus-screenshots.s3.amazonaws.com/s/78fd816ae5af167c7c25ed4c18d6dc7d.png)

#####(1) Content Description

This is Redemption Description. In this section drag and drop multiple elements from left panel like images, Events, text, heading, blocks or dynamic blocks etc and create an designed content for Description.

#####(2) Content Precautions

This is Redemption Precautions. In this section drag and drop multiple elements from left panel like images, Events, text, heading, blocks or dynamic blocks etc and create an designed content for Precautions.

Form Part 3 SEO

![seo-section](https://nimbus-screenshots.s3.amazonaws.com/s/67596afd22b4559a3d4b8afbc75f97bf.png)

#####(1) URL Key

Add URL identifer or leave it black it will create the identifer according to the Redemption Title

#####(2) Meta Title

Add Meta Title

#####(3) Meta Keywords

Add Meta Keywords

#####(4) Meta Description

Add Meta Description

##### Diffrent Save Buttons

![save](https://nimbus-screenshots.s3.amazonaws.com/s/affa68a2334ca62d249958b6f18115fd.png)

At the end click on save button to save the Redemption information.

There are three different buttons to save the Redemption information

##### Save

Click Save button to save Redemption information.

##### Save & Duplicate

Click Save & Duplicate option to save Redemption information and create a copy of current Redemption we can change some information and add another Redemption.

##### Save & Close

Click Save & Close option to save Redemption information and then close the add new form and it will redirect to admin grid page. 

##### Back

and Back button is used to go back on Redemption Manager Manage Grid and it will not save the Redemption information.

## 2) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/aeb48b3eb0aaae569239167f079caf23.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. or two modified dates. Also you can filter data according to the store scope and Redemption status.
Add the parameters and click Apply Filters Button

## Action Column

For Redemption delete and edit, go in the last column of Grid **Action** click the **select** Arrow then show two options edit, delete and view. Where Redemption can be edit, delete or view.

## 4) Edit 

Edit Action is used to edit the the existing record by clicking edit it opens the form with current data where we can add changes and update the data.

## 5) Delete

Delete Action is used to delete the selected record.

## 6) View

View Action is used to see the frontend registration page where customer can register.

#Redemption Frontend Examples

The link to view fontend page is when click on View Action from action tab it shows the following page

![register-form](https://nimbus-screenshots.s3.amazonaws.com/s/b49d0f7c7cc8c6c40575c845b1e32090.png)

## 1) CMS Block

This is showing the CMS block which admin select form the list of CMS block when add a new redemption

## 2) Descriptiopn Area

This is showing the Descriptiopn which admin adds when add a new redemption

## After description 

After Description at frontend shows the field for registration field which customer fills with their personal details

## 1) Add Customer Name

## 2) Add Customer Phone Number

## 3) Add Customer Email

## 4) Select the counter where customer wants to register the redemption

## 5) Line Id it is optional

## Precautions Area

This is showing the Precautions which admin adds when add a new redemption

## 6) Google Recaptcha

For Validation

## 7) Submit Button

After filling the form click on submit button it saves the redemption in the admin redmption form at the bottom which shows the grid of registered customer as shown in the image

![registered-customer](https://nimbus-screenshots.s3.amazonaws.com/s/401b210df84d870646b753d4e5cfc3a1.png)

also it send the email and sms to the customer with a link where customer can confirm the redemption

the email template example is as shown in the image

![email-template](https://nimbus-screenshots.s3.amazonaws.com/s/a8e8326a118ef30f9e7de4679aa327a9.png)

if a user did not receive an email or sms he can click on resend email/sms button after counter time complete. It will resend the email and sms to the registered customer.
And to resend the email/sms recaptcha is required.

![disable-button-counter](https://nimbus-screenshots.s3.amazonaws.com/s/a37a2e90b07b2a5dfdbb6dc56fc0e5f8.png)

and when the timer complete the resend email/sms button will be enable.

![enable-button-after-counter](https://nimbus-screenshots.s3.amazonaws.com/s/11970c5f76b94cc0223e8807da9a9be9.png)

And when user receive an email then click on confirmation link that will open a form and show a confirm button after click on confirm button the redemption process will complete and a customer registered for the redemption 

![confirm-button](https://nimbus-screenshots.s3.amazonaws.com/s/408c930d94531ca7f2685757307245ea.png)
