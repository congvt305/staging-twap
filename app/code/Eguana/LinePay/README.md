Eguana_LinePay

Website: Amore

Author: Muhammad Umer Farooq

Explanation: This module will allow the customer to place order using line pay

# LINEPay

Description:

Register module will be mainly used to allow the customer to place order using line pay.

Requirements:

    - User should be able to place order using line pay
    - Admin should be able to make refund using line pay
    - User should be able to checkout using TWD currency
    - User should be able to select e-invoice type  (greenworld, triplicate, donation, mobile barcode) 

Key features:

      1. Admin can enable or disable LINEPay
      2. Admin can change mode to sandbox or production
       
Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_LinePay

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration ⇾ SALES ⇾ Payment Methods ⇾ OTHER PAYMENT METHODS ⇾ LINE PAY**.

**Configuration**

1. Navigate to **Stores ⇾ Configuration ⇾ SALES ⇾ Payment Methods ⇾ OTHER PAYMENT METHODS ⇾ LINE PAY**.

 ![Configuration](https://i.ibb.co/t2k5cb1/1.png)
 
* Enable 

This is module main enable/disable button. This will decide either module is enable or disabled

* Mode 

Admin can change mode to production or sandbox

**Credentials**

* Channel ID

Enter the line pay channel id.

* Channel Secret key

Enter the line pay channel secret key.

Follow these steps in below link to create sandbox account and get channel id and secret key
https://pay.line.me/jp/developers/techsupport/sandbox/testflow

**Frontend**

After enabling LINEPay.
You can see LINE PAY payment button on checkout

 ![Checkout](https://i.ibb.co/4tbk2TV/2.png)

Click on Continue to LINE PAY to place the order.

You can login with LINE Login or Scan QR Code and you are redirected to payment page.

 ![LoginToLinePay](https://i.ibb.co/GM7nmxV/Screenshot-from-2020-11-19-17-32-59.png)

Enter the credit card details and pay the amount.

 ![LinePay](https://i.ibb.co/NF23Ytw/Screenshot-from-2020-11-19-17-33-16.png)

After you will be redirected to order success page.

**Admin**

Admin can see order transaction details with the following information.
e-invoice type  (greenworld, triplicate, donation, mobile barcode) 
method_title, linepay_order_id, method,	amount, maskedCreditCardNumber

 ![LinePay](https://i.ibb.co/ZBBjC9M/3.png)

Admin can make a refund request by viewing any order invoice and make a credit memo.

 ![LinePay](https://i.ibb.co/C80C09q/4.png)

## Test Cases

### Test case to test Mobile phone barcode containing + sign
1. From mobile place an order and on payment page select line pay as payment method and check Personal cloud invoice.
2. Mobile phone barcode carrier field will appear. Add the valid barcode containing "+" sign on any position.
3. Submit the order.
4. From admin side go to the Sales > Order > Order details and within transaction details check for "ecpay_einvoice_cellphone_barcode" value.  
5. **Expected Result:** Barcode must be same as entered while placing order containing "+" sign.
