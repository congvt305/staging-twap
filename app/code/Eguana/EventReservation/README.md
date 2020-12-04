## Event Reservation

`Website` : Main Website URL 
`Author` : Raheel Shaukat  
`DB Table Name` : eguana_event_reservation, eguana_event_reservation_store, eguana_event_reservation_counter, eguana_user_event_reserve

####Description:

Eguna Event Reservation allows you to create & manage the events for available stores on differnet dates, time slots and allow user to reserve the events from sites. 

####Key features:

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
 **General Configurations**
  
Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION TAB ⇾ Events Reservation in the left panel.

![event-reservation-config](https://i.ibb.co/GMr60qH/screenshot-local-stw-magentoshop-net-2020-11-12-13-44-48.png)

![event-reservation-config](https://nimbus-screenshots.s3.amazonaws.com/s/b474d53b0d4e702ff1bad7aa56b289b1.png)

Add configuration values in the following fields and click the Save Config button.

#### General Configuration

![config-fields](https://nimbus-screenshots.s3.amazonaws.com/s/133f433e230ed00da0641a30d5f38eb2.png)

#####(1) Enable or Disable module

Admin can enable or disable module from Enable feature.

#####(2) Select Email Sender

#### Email Template Configuration

Admin can select email sender from Email Sender field.

#####(1) Send Email to Staff Auto

Admin can enable or disable automatically email sending to staff.

#####(2) Staff Email

Admin can add default email of staff.

#####(3) Staff Email Template Pending

Admin can set staff pending email template.

#####(4) Staff Email Template Confirmed

Admin can set staff confirmed email template.

#####(5) Staff Email Template Canceled

Admin can set staff canceled email template.

#####(6) Send Email to Staff Auto

Admin can enable or disable automatically email sending to customer.

#####(7) Customer Email Template Pending

Admin can set customer pending email template.

#####(9) Customer Email Template Confirmed

Admin can set customer confirmed email template.

#####(9) Customer Email Template Canceled

Admin can set customer canceled email template.


## Manage Events (Admin Side)

Navigate to **Content­ ⇾ Manage Store Contents** and click on Events Reservation

![event-reservation](https://nimbus-screenshots.s3.amazonaws.com/s/9ec309007eedabb4856933e8ddbb646b.png)

It will open a Manage Events Grid. In the grid all Events will be shown which are created by admin.

Example image of an admin grid is shown below.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/46701f9c6a2dd90985dc07e735ddda10.png)

#### Explore The Grid

##### 1) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/aafa82dc16e97b105e7032ff23766678.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

##### 2) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/809049bc047b82c57aad4f2e918a5e14.png)

Filter option is used to search data but in this you can select range of options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Also you can filter data according to the Event title, Event status, Stores, Created & Modified dates.
Add or select the parameters and click Apply Filters Button.

##### 3) Action Column

![action](https://nimbus-screenshots.s3.amazonaws.com/s/1d4f74f4d3cb24bd797dc612755130c3.png)

For updation, deletion or view event details on site, go in the last column of grid **Action** click the **select** link. It will show three options Edit, Delete and View. By these you can add event add, delete and view Event respectively.

#### Add / Update Event and Counters

##### 1) Add / Update Event

![add-event](https://nimbus-screenshots.s3.amazonaws.com/s/732eb77ac04bd97d9a8243013330345b.png)

By clicking on Add New Event from grid a form will be displayed on which you can set enable or disable event by Enable button, add title, upload an event image or select from gallery. Select CMS Block (which will be shown on Reservation form page on site), add description & select stores and save button.

##### 2) Add / Update Counter for Event

![counter-grid](https://nimbus-screenshots.s3.amazonaws.com/s/7274694b6f289e1ac3f1805110411489.png)

When an event is created then you can able to add counters for this event. Expand the Event Reservation Counters tab a grid will be shown there in which all the counters available for events are listed. By clicking edit button from the grid Action column a pop up form will show by which you can add/update the counter details and save it. After saving that counter will be listed in grid.

By selecting status enable or disable that counter will be shown/hide on reservation form on sites.

![add-counter](https://nimbus-screenshots.s3.amazonaws.com/s/ba1618ce8daebd7a29c214511386594e.png)

![counter-grid](https://nimbus-screenshots.s3.amazonaws.com/s/4adb7744a4292c8f80897aebfae51054.png)

###### Some Important Points While Adding Counter Details

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

#### Explore Customer's Reservation Grid

##### 1) Search by keyword

![user-reserve-grid](https://nimbus-screenshots.s3.amazonaws.com/s/9934f5f02127b36c84f74e7d2bd24e28.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

##### 2) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/2348c28ce0803d301e448eb3b7f10466.png)

Filter option is used to search data but in this you can select range of options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Also you can filter data according to the Store Counter Name, Customer Name, Customer Email, Time Slot, Customer Phone No, Reservation Status, Stores and Agreement check.
Add or select the parameters and click Apply Filters Button.

##### 3) Export

![export](https://nimbus-screenshots.s3.amazonaws.com/s/51f5f6f8d8b847b5de532daeb8ea8dad.png)

You can export the list of customers which have submitted the request of reservation by clicking on export button in cvs, xml and xls form.

## Reserve Events (From Sites)

Customer can reserve the event by visiting the site link provided to them. A form will be shown on which name, email, phone no, counter name, counter date, time slots and agreement fields are mentioned. When customer select the counter from counter field then available dates will be mention in date field and by selecting the date all time slots against that date will be shown. Time slots which are disabled in time slots field indicate that those seats are all reserved.

![reservation-form](https://nimbus-screenshots.s3.amazonaws.com/s/65708287e396c4df0f2738409e96aa96.png)

When customer submit the form a confirmation email will be send to the customer as well as to the staff admin email if those are set enable from the configration. By confirming/canceling the event from email, an email will again send to both customer and staff admin about confirmation or cancelation.

#### Some Important Points

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
