# SAP

`Website` : Main Website URL  
`Author` : Brian, Raheel Shaukat  

## Stock Sync REST API

SAP use this api to update multiple stocks in a single request in magento.

### Request Parameters
{
    "source" : "string",
    "mallId" : "string",
    "stockData" : [
        {
            "matnr" : "string",
            "labst" : "string"
        },
        {
            "matnr" : "string",
            "labst" : "string"
        },
        ...
    ]
}

1. **source :** SAP Store code
2. **mallId :** SAP Mall Id
3. **matnr :** Material Number (SKU)
4. **labst :** Available Quantity

### Response

The response which can be be occurs are as follow:

### Success

Two type of success response will be occurs:

#### 1. 1st with all stock quantity updated succefully.

{
    "code" : "0000",
    "message" : "SUCCESS",
    "data" : []
}

1. **code :** Success code
2. **message :** SUCCESS

#### 2) 2nd with some stock quantity updation failure message and others with success message.

If any of matnr value is incorrect or labst value is either negative or not number then this response will be occurs:

(for example)
{
    "code" : "0000",
    "message" : "SUCCESS",
    "data" : [
        {
            "message": "Input data is empty",
        }
    ]
}

1. **code :** 0000
2. **message :** SUCCESS
3. **data :** matnr will be SKU and messgae will be "SUCCESS" if stock updated successfully or it will be an exception message which occurs while updating stock.

### Errors

When call fails the following errors will be occurs:

#### 1) Stock data missing (error code 0001)

If data is incomplete.

{
    "code" : "0001",
    "message" : "Stock data missing",
    "data" : []
}

1. **code :** Error code 0001
2. **message :** Stock data missing (either source, mallId or stockData values are missing or stockData is not array)

#### 2) Incorrect mallId (error code 0002)

If mallId is incorrect.

{
    "code" : "0002",
    "message" : "Mall Id %id is not specified or incorrect",
    "data" : []
}

1. **code :** Error code 0002
2. **message :** Mall Id (mallId value) is not specified or incorrect

#### 3) SAP configuration are disabled (error code 0003)

![sap-config](https://nimbus-screenshots.s3.amazonaws.com/s/a21d732afb0a54e73155141d71f8f39e.png)

If in SAP configuration "Enabled Request URL" is diabled or "Stock Data Get From SAP Active" field is disabled then this error occurs.

{
    "code" : "0003",
    "message" : "Configuration is not enabled",
    "data" : []
}

1. **code :** Error code 0003
2. **message :** Configuration is not enabled

## Update GI Logic Plugin

`Website` : Main Website URL  
`Author` : Raheel Shaukat

### Description

This plugin is define to get stock info from SAP and update stock in magento when GI call occurs. This plugin uses SYNC Stock API (update bulk stock). The API to get stock info BizConnect Operation Log Topic Name is "amore.sap.get.stock.data" for verifying API response.

### Configuration

These configurations are store base.

![plugin-config](https://nimbus-screenshots.s3.amazonaws.com/s/aa933536b70d8efeef2038ba477a7a22.png)

