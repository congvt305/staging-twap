# Event Reservation

`Website` : Main Website URL

`Author` : Raheel Shaukat

`DB Table Name` : eguana_event_reservation, eguana_event_reservation_store, eguana_event_reservation_counter, eguana_user_event_reserve

##Description:

Eguna Event Reservation allows you to create & manage the events for available stores on differnet dates, time slots and allow user to reserve the events from sites.

##Key features:

- Admin can Enable or Disable Events, To either show an event on front end or not to show.
- Admin can add Event Title, Event Image, CMS Block Event Start and End Date, Store available, Description, URL Key, Meta Title, Meta Description, Meta Keywords and select store at which admin wants to show the Event.
- After saving Event admin can add counter details like Staff Email (for event reservation emails), From & To Date, Time Slot, Break, Per Time Slot Seats, Start Time, End Time and CLose Days. And also enable or disable the counter.
- Admin can export the list of customers which reserved the specific event in CVS, Xml and Xls form.
- Customers can reserve the event on basis of event link provided to them.
- Customer reserve event by adding name, email, phone no, selecting specific counter which are listed in dropdowm, select the event date and time slot which are available.
- A confirmation email will be send to the customer as well as on staff admin email which will be mention against Event's Counter detail.
- Customer can confirm as well as cancel his/her reservervation by clicking links mentioned in the email.
- Staff Admin can also confirm as well as cancel his/her reservervation of customer by clicking links mentioned in the email.
- Confirmation/Cancelation emails will be send to both customer & staff admin whenever these actions are performed.

## Module Installation
```
1.  php bin/magento Module:enable Eguana_EventReservation  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```
## Configurations

Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION TAB ⇾ Events Reservation in the left panel.

![event-reservation-config](https://i.ibb.co/GMr60qH/screenshot-local-stw-magentoshop-net-2020-11-12-13-44-48.png)

![event-reservation-config](https://nimbus-screenshots.s3.amazonaws.com/s/b474d53b0d4e702ff1bad7aa56b289b1.png)

Add configuration values in the following fields and click the Save Config button.

## General Configuration

![general-config](https://nimbus-screenshots.s3.amazonaws.com/s/50a8e93419dbc0a2436a6363331f121e.png)

###(1) Enable or Disable module

Admin can enable or disable module from Enable feature.

###(2) Select Email Sender

Admin can select email sender from this field.

###(3) Privacy Policy

Admin can change and set the privacy policy content by this text area field.

###(4) Resend Emai And Sms Interval

Admin can set the time interval to enable the resend button so customer can click resend email/sms again if he/she not recieve sms or email.

## Email Template Configuration

![email-temp-config](https://nimbus-screenshots.s3.amazonaws.com/s/641b9b8b521811b7a4be656c06b60f25.png)

###(1) Send Email to Staff Auto

Admin can enable or disable automatically email sending to staff.

###(2) Staff Email

Admin can add default email of staff.

###(3) Staff Email Template Pending

Admin can set staff pending email template.

###(4) Staff Email Template Confirmed

Admin can set staff confirmed email template.

###(5) Staff Email Template Canceled

Admin can set staff canceled email template.

###(6) Send Email to Staff Auto

Admin can enable or disable automatically email sending to customer.

###(7) Customer Email Template Pending

Admin can set customer pending email template.

###(8) Customer Email Template Confirmed

Admin can set customer confirmed email template.

###(9) Customer Email Template Canceled

Admin can set customer canceled email template.

## SMS Template Configuration

![sms-temp-config](https://nimbus-screenshots.s3.amazonaws.com/s/52d7d192d5cadeb96ec1a9f180770e8f.png)

###(1) Send SMS to Customer Automatically

Admin can enable or disable automatically sms sending to customer feature.

###(2) Reservation SMS

Admin can set customer reservation sms template.

### Note

These all configurations are store base.

## Manage Events (Admin Side)

Navigate to **Content­ ⇾ Manage Store Contents** and click on Events Reservation

![event-reservation](https://nimbus-screenshots.s3.amazonaws.com/s/9ec309007eedabb4856933e8ddbb646b.png)

It will open a Manage Events Grid. In the grid all Events will be shown which are created by admin.

Example image of an admin grid is shown below.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/46701f9c6a2dd90985dc07e735ddda10.png)

## Explore The Grid

### 1) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/aafa82dc16e97b105e7032ff23766678.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button.

### 2) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/809049bc047b82c57aad4f2e918a5e14.png)

Filter option is used to search data but in this you can select range of options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Also you can filter data according to the Event title, Event status, Stores, Created & Modified dates.
Add or select the parameters and click Apply Filters Button.

### 3) Action Column

![action](https://nimbus-screenshots.s3.amazonaws.com/s/1d4f74f4d3cb24bd797dc612755130c3.png)

For updation, deletion or view event details on site, go in the last column of grid **Action** click the **select** link. It will show three options Edit, Delete and View. By these you can add event add, delete and view Event respectively.

## Add / Update Event and Counters

### 1) Add / Update Event

![add-event-1](https://nimbus-screenshots.s3.amazonaws.com/s/45ca28c26dedbba97a407f5a9e80393e.png)

By clicking on Add New Event from grid a form will be displayed on which you can:

#### (1) Enable

Set enable or disable event by Enable button.

#### (2) Title

Add title of event.

#### (3) Image

Upload event image or select from gallery.

#### (4) Success Image

Upload event success image which will be shown in place of event image if customer successfully reserved the event.

#### (5) CMS Block

Select CMS Block (which will be shown on Reservation form page on site).

#####(6) SMS Content

This is for adding the content of sms which is to be send to customer after event is reserved. By default a content is set in this field for explanation.
Here "%store" will be the current store name from which reservation is made, "%eventName" will be the current event name, "%confirm" be the confirmation URL for reserving the event and "%cancel" means the cancelation URL.

#### (7) Description

![add-event-2](https://nimbus-screenshots.s3.amazonaws.com/s/3ff57aa474dfc3cbd8b741bfd0bacd47.png)

Add description (using page builder)

#### (8) Event in Websites

![add-event-3](https://nimbus-screenshots.s3.amazonaws.com/s/c0e1ab62a373c6612311518cab736244.png)

Select webiste's store on which event form will be shwon. When store is selected from field its related counters will be list down in "Event Reservation Counters" tab which can only be edited after saving the event detail.

### 2) Add / Update Counter for Event

![counter-grid](https://nimbus-screenshots.s3.amazonaws.com/s/7274694b6f289e1ac3f1805110411489.png)

When an event is created then you can able to add counters for this event. Expand the Event Reservation Counters tab a grid will be shown there in which all the counters available for events are listed. By clicking edit button from the grid Action column a pop up form will show by which you can add/update the counter details and save it. After saving that counter will be listed in grid.

By selecting status enable or disable that counter will be shown/hide on reservation form on sites.

![add-counter](https://nimbus-screenshots.s3.amazonaws.com/s/ba1618ce8daebd7a29c214511386594e.png)

![counter-grid](https://nimbus-screenshots.s3.amazonaws.com/s/4adb7744a4292c8f80897aebfae51054.png)

### Some Important Points While Adding Counter Details

While adding counter details user is required to fill the required fields with correct values else counter details will not add. Some of the examples are mentiod below:

**1)** If the slot time is greater than the time difference between start and end time then an error message like "The slot time should be less than difference between Start and End Time" will be shown on the top of the form.

![slot-time-error](https://nimbus-screenshots.s3.amazonaws.com/s/8893355abd951690b853311805fe9c23.png)

**2)** If the slot time are not exactly managed between Start and End Time then also an error message will be shown on the top of the form like this:

![slot-time-error-2](https://nimbus-screenshots.s3.amazonaws.com/s/07bb2d305fd91442e6ad1f86f988e9d1.png)

**3)** If Start Date and End Date of event are same and Start Time will be greater than the End Time then below error message will be shown on the top of the form:

![start-time-error](https://nimbus-screenshots.s3.amazonaws.com/s/3595ce8c6931de22d3ade12f3fdceeac.png)

**4)** If user select all days as closing days from the close day field then below error message will be shown on the top of the form:

![close-days-error](https://nimbus-screenshots.s3.amazonaws.com/s/2c5b79947c6b854ee8a526f0dd76a211.png)

**5)** While adding counter details staff email value will be the same as define in configuration but admin can change it. And staff emails will be send to that address (if enabled from configuration) which is saved across each counter detail.

![staff-email](https://nimbus-screenshots.s3.amazonaws.com/s/3b4bc818842d649d4768e1e7d031eef2.png)

## Explore Customer's Reservation Grid

### 1) Search by keyword

![user-reserve-grid](https://nimbus-screenshots.s3.amazonaws.com/s/be254bc453908c8afbb8245a3de3a6a9.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button.

### 2) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/c1d5b4e87790931a18e2e0a6fc8accd1.png)

Filter option is used to search data but in this you can select range of options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Also you can filter data according to the Store Counter Name, Customer Name, Customer Email, Time Slot, Customer Phone No, Reservation Status, Stores and Agreement check.
Add or select the parameters and click Apply Filters Button.

### 3) Export

![export](https://nimbus-screenshots.s3.amazonaws.com/s/51f5f6f8d8b847b5de532daeb8ea8dad.png)

You can export the list of customers which have submitted the request of reservation by clicking on export button in cvs, xml and xls form.

## Reserve Events (From Sites)

![reservation-form](https://nimbus-screenshots.s3.amazonaws.com/s/7df4aa8b89e1ef3a3b01339065bbbd94.png)

- Customer can reserve the event by visiting the site link provided to them.

- A form will be shown on which name, email, phone no, line id, counter name, counter date, time slots and privacy policy fields are mentioned.

- When customer select the counter from counter field then available dates will be mention in date field and by selecting the date all time slots against that date will be shown.

- Time slots which are disabled in time slots field indicate that those seats are all reserved.

- When customer submit the form a confirmation email will be send to the customer as well as to the staff admin email also sms will be send but to customer only if those are set enable from the configration.

- Success Image will be shown in place of event image if event is reserved successfully with success message.

![success-msg](https://nimbus-screenshots.s3.amazonaws.com/s/d61fd6d154ab1532dbeb049d95b85b3e.png)

- If customer try to reserve same counter with same date and time slot then error message will be shown to him/her.

![duplicate-msg](https://nimbus-screenshots.s3.amazonaws.com/s/d61fd6d154ab1532dbeb049d95b85b3e.png)

- By confirming/canceling the event from email, an email will again send to both customer and staff admin about confirmation or cancelation of the event.

- If email or sms didn't recieve to customer then he/she able to resend these by clicking the resend button again after the counter time is over. (Resend button enable counter time can be changed from configuration which is mention in above configuration section).

![resend-btn](https://nimbus-screenshots.s3.amazonaws.com/s/dab8660269378707fa8fbcfb6eaf3c3c.png)

## Some Important Points

**1)** When admin create an event against specific store. Add counter details in it then if a single user reserve this event then admin can't change its store. Example is in below picture.

![disabled-stores](https://nimbus-screenshots.s3.amazonaws.com/s/a42c0aa116725038ef82f9a1c5802057.png)

**2)** If any counter is expired then it will be shown disabled in the event form counters field.

![counter-expired](https://i.ibb.co/6W3jd6R/aaaa.png)

**3)** If all counters are expired then counters field will be empty and submit button will be disabled with expiry note at the bottom of button.

![event-expired](https://i.ibb.co/QXXKc6X/aaaabbb.png)

**4)** All dates before current date will also be shown disabled in dates field. Example is in below image:

![dates-disabled](https://i.ibb.co/0B8qDWw/dates-disabled.png)

**5)** The time slot for which all seats are reserved will also be shown disabled in time slots field. Example is in below image:

![time-slot-full](https://i.ibb.co/bX7MHFY/time-slot-disabled.png)

**6)** Customer is restricted to perform confirm or cancel only one action from the pending email request. Whenever he/she try to perform another action then an error message will be shown like "You are not allowed to perform this action".

![not-allowed](https://nimbus-screenshots.s3.amazonaws.com/s/afa137c9c747da457136688a01be109a.png)

**7)** The total no of slots against all counters will be sum up to form total available slots of an event which will be shown in Event Grid in available slots columnn. If counters days are past then there slots will be minus from the total result of available slots.

## Test Cases
- On Admin event listing page search by keyword works properly.
- On Admin event listing page search events by filters work properly.
- On Admin event listing events must be enable, disable or deleted by action field.
- When adding an event from admin panel url key duplication check must be handle and url key will be generated auto if not defined by admin.
- Admin can select event image from gallery as well as upload the image.
- The meta title, description & keyword must be included in the reservation form page.
- While adding counters validation checks must be present.
- Admin can search reserved user data by search field and also with the help of filters.
- Admin can export the list of reserved users.
- Module features are shown at sites only that time when it is enable from configuration.
- If an event is disabled then by accessing it from site it will display an error message about event is disabled and redirect to site main page.
- Only those events will be accessible on the sites which are related to that site else an error message will be shown and redirect to site main page.
- If the counter is disabled then it will not be shown it counter field in the reservation form.
- Those dates will not shown in date field which are on close days.
- The time slots which seats are reserved completly will be shown disabled in the time slot field.
- When customers submit the reservation form an email will be send to them and as well as staff admin if there configration are enabled on basis of current site.
- When confirm reservation from email then confirmation email will be send to the customer as well as staff admin and also same process for the cancelation of email.
- If customer didn't recieve sms or email after succefully reserving the event then click the resend button on reservation form for re-sending the email & sms.
