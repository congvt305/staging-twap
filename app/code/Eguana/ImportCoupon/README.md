# Import Coupon

`Website` : Main Website URL 
`Author` : Raheel Shaukat  

###Description:

Eguna Import Coupon allows you to import sale rule coupon codes using csv file after creating sale rule.

## Module Installation  
```
1.  php bin/magento Module:enable Eguana_ImportCoupon  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile
```

## To Import Coupoon Codes

To import coupon code against a rule:

1) Create a new cart price rule and select "Specific Coupon" option from the Coupon dropdown field. An input field with label "Coupon Code" and a checkbox "Use Auto Generation" will be shown.
2) Mark check "Use Auto Generation" checkbox and fill the remaining fields.
3) Click "Save and Continue Edit".

![create-rule](https://nimbus-screenshots.s3.amazonaws.com/s/fd7f8da62a519de95d3edb1a3ce87ee6.png)

4) After saving the rule "Import Coupon Codes" tab will be visible under "Manage Coupon Codes" tab.

![import-coupon-code-tab](https://nimbus-screenshots.s3.amazonaws.com/s/f3294e523075677cff59dec893af9288.png)

5) Now press upload button and upload a csv file containing coupon codes. Each coupon code must be on a new line and "Coupon Code" text will be there at first row of csv file. After uploading file click "Save and Continue Edit" button.

![upload-coupon-code-file](https://nimbus-screenshots.s3.amazonaws.com/s/8816e5add2ea882513534bd0ebbbc85b.png)

6) The imported coupon codes will be shown in the grid under "Manage Coupon Codes" tab after saving the rule.

![code-grid](https://nimbus-screenshots.s3.amazonaws.com/s/2047082d22e29486017d7a895a43f533.png)

** Same proceess will be followed while updating the rule**

## Important Points

1) "Import Coupon Codes" tab will only be shown after creating new rule or in existing rule. Else it will be hidden.

2) Upload button will remain be disabled untill "Specific Coupon" option from the Coupon dropdown field is selected along with "Use Auto Generation" checkbox marked checked.

3) Each coupon code must be on a new line and "Coupon Code" text will be there at first row of csv file.

4) Only csv file should be uploaded with correct format for importing coupon codes.

## Test Cases

- "Import Coupon Code" should not be shown while creating new sale rule.
- If "Specific Coupon" option from the Coupon dropdown field is not selected or "Use Auto Generation" checkbox not checked while updating existing rule then upload file button will remain disabled.
- Uploading file other than csv extion will shown invalid extension popup message.
