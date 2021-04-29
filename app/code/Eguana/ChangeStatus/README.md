# Change Status

## Change Status to "delivery_complete"

`Website` : Main Website URL  
`Author` : Raheel Shaukat

### Description

To change that order status to "delivery_complete" of those orders whose GI issued (order status = shipment_processing) and having shipment method BlackCat (with GI issued from last 7 days) or shipment method CVS with status notification is 3022 or 2067.

### Configurations

Navigate to **Stores­ ⇾ Configuration ⇾ Change Status**

![config](https://nimbus-screenshots.s3.amazonaws.com/s/cc4cd6cdf6bdd60456246beca6cc3ca4.png)

#### 1) Change Order Status Active

To enable/disable cron run.

#### 2) Frequency

To set frequency for cron job.

#### 3) Start Time

To set time for cron to start.

#### 4) Run Now

To instantly run the cron process for changing order status.
