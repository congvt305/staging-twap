<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 23/7/20
 * Time: 7:07 PM
 */
namespace Eguana\Pip\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Manage customers data for policies and consents
 *
 * Class Customers
 *
 */
class Customers
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Customers constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Encrypt customer data
     * @param $id
     * @return $this
     */
    public function updateCustomerInformation($id)
    {
        $connection  = $this->resource->getConnection();
        //Encrypt customer
        $tableName = $connection->getTableName("customer_entity");
        $sql = "Update " . $tableName . " Set email = '" . $id . "*****@***.***', firstname = '***', middlename = '***', lastname = '***', dob = '***'  where entity_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt customer mobile no
        $tableName = $connection->getTableName("customer_entity_varchar");
        $tableName1 = $connection->getTableName("eav_attribute");
        $sql = "Update " . $tableName . " Set value = '***********' where entity_id = '" . (int)$id . "' and attribute_id in (SELECT attribute_id FROM " . $tableName1 . " WHERE attribute_code = 'mobile_number')";
        $connection->query($sql);
        //Encrypt customer grid
        $tableName = $connection->getTableName("customer_grid_flat");
        $sql = "Update " . $tableName . " Set email = '" . $id . "*****@***.***', name = '***', billing_firstname = '***', billing_lastname = '***', billing_telephone = '***********', dob = '***'  where entity_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt orders
        $tableName = $connection->getTableName("sales_order");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', customer_firstname = '***', customer_lastname = '***', customer_middlename = '***', customer_prefix = '***', customer_suffix = '***', customer_dob = '***'  where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt orders grid
        $tableName = $connection->getTableName("sales_order_grid");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', shipping_name = '***', billing_name = '***', customer_name = '***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt sales order address
        $tableName = $connection->getTableName("sales_order_address");
        $tableName1 = $connection->getTableName("sales_order");
        $sql = "UPDATE " . $tableName . " SET lastname = '***', email = '" . $id . "*****@***.***', firstname = '***', middlename = '***' where parent_id in (SELECT entity_id FROM " . $tableName1 . " WHERE customer_id = '" . (int)$id . "')";
        $connection->query($sql);
        //Encrypt invoice grid
        $tableName = $connection->getTableName("sales_invoice_grid");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', customer_name = '***', billing_name = '***' where order_id in (SELECT entity_id FROM " . $tableName1 . " WHERE customer_id = '" . (int)$id . "')";
        $connection->query($sql);
        //Encrypt shipment grid
        $tableName = $connection->getTableName("sales_shipment_grid");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', customer_name = '***', billing_name = '***', shipping_name = '***' where order_id in (SELECT entity_id FROM " . $tableName1 . " WHERE customer_id = '" . (int)$id . "')";
        $connection->query($sql);
        //Encrypt creditmemo grid
        $tableName = $connection->getTableName("sales_creditmemo_grid");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', customer_name = '***', billing_name = '***' where order_id in (SELECT entity_id FROM " . $tableName1 . " WHERE customer_id = '" . (int)$id . "')";
        $connection->query($sql);
        //Encrypt rma grid
        $tableName = $connection->getTableName("magento_rma_grid");
        $sql = "Update " . $tableName . " Set customer_name = '***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt newsletter subscriber
        $tableName = $connection->getTableName("newsletter_subscriber");
        $sql = "Update " . $tableName . " Set subscriber_email = '" . $id . "*****@***.***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt review detail
        $tableName = $connection->getTableName("review_detail");
        $sql = "Update " . $tableName . " Set nickname = '***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt customer address entity
        $tableName = $connection->getTableName("customer_address_entity");
        $sql = "Update " . $tableName . " Set firstname = '***', lastname = '***', middlename = '***', telephone = '***********' where parent_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt quote
        $tableName = $connection->getTableName("quote");
        $sql = "Update " . $tableName . " Set customer_email = '" . $id . "*****@***.***', customer_firstname = '***', customer_lastname = '***', customer_middlename = '***', customer_dob = '***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt quote address
        $tableName = $connection->getTableName("quote_address");
        $sql = "Update " . $tableName . " Set email = '" . $id . "*****@***.***', firstname = '***', lastname = '***', middlename = '***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        //Encrypt magento invitation
        $tableName = $connection->getTableName("magento_invitation");
        $sql = "Update " . $tableName . " Set email = '" . $id . "*****@***.***' where customer_id = '" . (int)$id . "'";
        $connection->query($sql);
        return $this;
    }
}
