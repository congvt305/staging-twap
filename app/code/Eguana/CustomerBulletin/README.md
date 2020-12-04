# Eguana Customer Bulletin

`Website` : Main Website URL 
`Author` : Bila Younas  
`DB Table Name` : eguana_ticket , eguana_ticket_note 
`Listing Page`  : Ticket listing page will be "{Main Website URL}/ticket" once module is activated'

####Description:

Eguna Customer Bulletin allows you to Create ticket of problem that you face in using the wesite and ask questions from
admin you can also close ticket.

####Key features:

- Admin can open or close ticket.
- Admin can add Note in the Ticket and give answers of questions which are ask by customer.
- Admin can also add attachment in Note.
- Admin can download the attachment which is attached with note.
- Customer can create and close ticket.
- Customer can add subject of ticket and select category which is created from the configuration and add message and also add attachment when he create a new ticket.
- Customer can see its all created tickets in ticket listing page.
- Customer can see subject category and ticket status and also note status in ticket listing page.
- Customer can add Note in the Ticket if he have any question.
- Customer can also add attachment in Note.
- Customer can download the attachment which is attached with note.
- FAQ list qustions list also show at the top of ticket create page and when customer click on the question he jumped on the Faq question answer page.
- Customer can add note in ticket from detail ticket page.
- Emails are send to the customer and staff at different point like when customer create ticket an email is send to customer and staff that email is send only at that condition if emails are enable from the configuration that emails are also send when customer add reply in ticket and close the ticket
- When customer add a note in the ticket than the status of note show unread at admin panel grid and also like that when admin add note in ticket the status of note sohe unread at customet ticket listing page.
- When customer or admin see the note the status of note become read.

#Module Installation  
```
1.  php bin/magento Module:enable Eguana_CustomerBulletin  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```
 **General Configurations**
  
Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION ⇾ Support Ticket in the left panel.

![customer-bulletin-config](https://i.ibb.co/BPj8Kt0/Screenshot-1.png)
![customer-bulletin-config](https://i.ibb.co/415j4CQ/Screenshot.png)

Add configuration Values in the following fields and click the save button.

**Ticket Configuration Group**

![config-fields](https://nimbus-screenshots.s3.amazonaws.com/s/0ad2c4feb07ce64788e80e1d7970c9eb.png)

#####(1) Enable or Disable module

Admin can enable and disable module from Enable Feature.

#####(2) Select Email Sender	

Admin can select email sender from Email sender Field.

#####(3) Create Category.

Admin can create and delete categories of tickets from categories field.

#####(4) Add file Extension Allow.

Admin can add files extension which customer can upload while create ticket or add notes.

#####(5) Add file size Allow.

Admin can add files maximum size which customer can upload while create ticket or add notes.

#####(6) Admin Name.

Admin name that show at frontend in ticket detail page.

#####(7) Sort order.

here you can set sort order of ticket listing at frontend.

#####(8) Clsoed Ticket Action Duration.

Admin can add Number of days for ticket is there is no communication then ticket will be delete by cron.

**Customer Email Template Configuration Group**

![config-fields](https://i.ibb.co/RPRH4h3/email-configuration.png)

#####(1) Send Email To Customer Automatically.

![config-fields](https://i.ibb.co/vxQfM0S/enable-email.png)

Admin can enable or disable Email which is send to the customer when admin add reply in any ticket.

#####(5) Select customer email template.

![config-fields](https://i.ibb.co/RPRH4h3/email-configuration.png)

Admin can select email template for customer when admin add reply in ticket..

**Closing Ticket Configuration**

![config-fields](https://i.ibb.co/N99pzqz/cron.png)

#### Close Ticket Cron Frequency

Admin can select cron time when run day weekly or monthly.

#### Cron Start Time

Admin can add the when cron run.

#### Run Cron

Admin can run cron from the by buttom "Run Now".

**Description for closing Tecket configuration**

THis congiuration is use to close for on the bases of the  "Clsoed Ticket Action Duration" number of days add.

#### Test Case:

Clsoed Ticket Action Duration = 2;

if a ticket create before tow daus and haven't any communication from last 2 days then this cron will automatically close 
this ticket.


#  Manage Support Ticket

Go to Customer > Eguana and click on Support Ticket

![customer-bulletin](https://i.ibb.co/xHFCk4R/Screenshot-3.png)

It will open a Manage Tickets Grid. In the grid all Tickets will be shown which are created by customers fron frontend.

Example image of an admin grid is below.

![grid](https://i.ibb.co/ZmMGSch/1.png)

 **Explore The Grid**

## 1) Search by keyword

![search](https://i.ibb.co/jD6jzy4/2.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

![filters](https://i.ibb.co/7Q5vw06/4.png)

Filter option is used to search data but in this you can select range of options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Also you can filter data according to the customer Name and Subject category and ticket status and also note status.
Add the parameters and click Apply Filters Button

## Action Column

For Ticket delete and View Detail of ticket, go in the last column of Grid **Action** click the **select** Arrow then show two options detail and delete. Where you can add note and see detail of ticket or delete.

![action](https://nimbus-screenshots.s3.amazonaws.com/s/8685414250ce64277159f1e5829fd3ab.png)

## 5) Delete

Delete Action is used to delete the selected tickets.

## 6) Print Order
export report of ticket in csv.

## 7) Close

Close Action is used to Close the selected tickets. 
## 8) Open

ReOpen Action is used to ReOpen the selected tickets.

#Ticket Frontend Examples

The link to view fontend page is {Main Website URL}/ticket
  
   **Listing Page**
 
(1) This is the list of all the tickets records which were created by customer. also shows the subject, category, ticket status, note status and action.

![listing-page](https://i.ibb.co/ChtqYFJ/6.png)
    
   **ADD New Ticket**
    
For adding the new ticket a button is shown at the top of the ticket listin page click on it that redirect to a new page at ticket creation page first faq questions list when you click on the question that redirect you on the answer of the question after faq questions list subject categoru and massage fields are present for creating a new ticket

![Add New-Ticket-page](https://i.ibb.co/TtqZxQD/Screenshot-9.png)
    
   **View Ticket**
    
For view for ticket you click on view button which is shown in the of the ticket when you click on the view button that button redirect you on detail page at detail page you can see the detail of the page .

![view-ticket-page](https://i.ibb.co/10qsHgK/7.png)

 **Add Note**

At detail page here two page are shown add note and close ticket when you click on the add note button a grid is show at the bottom of the add note button in that grid you add a message and attach file and then click on th create button a new note is create that show in detail page

![add-note-page](https://i.ibb.co/PZsdfM1/8.png) 
 
  **Close Ticket**
By close ticket you can close the ticket when a ticket is close then you can not add note in the ticket

 ![close-ticket-page](https://i.ibb.co/P6Fxfsn/9.png)

# Test Cases
- That module features are shown at frontend only that time when its is enable from configuration.
- Customer can not Add ticket without login at website.
- All tickets of customer are shown at {Main Website URL}/ticket page
- When customer Click on view page he redirect on detail page at detail page he can see ticket detail and note which are attched with ticket in descending order
- On detail page when customer click on the Add note button a grid is show below the add note button in that grid you can write your message and attach file with that Note
- On detail page when customer click on Close this ticket Button then that ticket will be close and add note and close tis ticket buttons are hide for that ticket and status of ticket show close 
- In listing page at frontend filed is shown with name note status that status will change to unread when admin add a note in ticket and customer does note seen that note when customer seen that note then status of note become read
- Close, view add note feature are same behave at admin side like frontend and also note status which is show in admin grid list
- An email send to customer when admin add reply in ticket.
