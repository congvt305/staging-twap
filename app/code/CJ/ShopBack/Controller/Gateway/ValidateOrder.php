<?php
declare(strict_types=1);
namespace CJ\ShopBack\Controller\Gateway;

use Hoolah\Hoolah\Controller\Main as HoolahMain;
use Hoolah\Hoolah\Helper\API as HoolahAPI;
use Hoolah\Hoolah\Model\Config\Source\OperationMode as OperationMode;
use Hoolah\Hoolah\Model\Config\Source\OrderMode;
use Magento\Framework\Api\SimpleDataObjectConverter;

class ValidateOrder extends \Hoolah\Hoolah\Controller\Gateway\ValidateOrder
{

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = array();

        $quote = $this->checkoutSession->getQuote();

        try
        {
            if (!$quote->getShippingAddress()->getShippingMethod()) {
                throw new \Exception('Shipping method is missing');
            }
            if ($this->hdata->credentials_are_empty())
                throw new \Exception('Merchant credentials are empty', 9999);

            $api = new HoolahAPI(
                $this->hdata->get_merchant_id(),
                $this->hdata->get_merchant_secret(),
                $this->hdata->get_hoolah_url()
            );

            $response = $api->merchant_auth_login();
            if (!HoolahAPI::is_200($response))
                throw new \Exception('Merchant auth error ('.HoolahAPI::get_message($response).')', 9999);

            $requestData = $this->request->getBodyParams();

            $guestEmail = @$requestData['guestEmail'];
            if (empty($guestEmail))
                $guestEmail = @$requestData['customerData']['email'];

            if ($guestEmail)
            {
                $requestData['billingAddress']['email'] = @$requestData['billingAddress']['email'] ? $requestData['billingAddress']['email'] : $guestEmail;
                $requestData['shippingAddress']['email'] = @$requestData['shippingAddress']['email'] ? $requestData['shippingAddress']['email'] : $guestEmail;
            }

            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();

            $this->updateAddress($billingAddress, $requestData['billingAddress']);
            $this->updateAddress($shippingAddress, $requestData['shippingAddress']);

            $billing = $billingAddress->getData();
            $shipping = $shippingAddress->getData();

            $debug = strpos($billing['firstname'], 'hoolah_debug') !== false;

            // get phone code
            $code = null;
            $code_country = null;
            $phone = preg_replace('/ /', '', $billing['telephone']);
            $phone_cleared = preg_replace('/[^0-9]/', '', $phone);

            $country_codes = HoolahMain::data('country_codes.php');
            $clear_country_codes = array();
            foreach ($country_codes as $country_code)
                if ($country_code['dial_code'])
                    $clear_country_codes[preg_replace('/[^0-9]/', '', $country_code['dial_code'])] = $country_code['code'];

            foreach ($clear_country_codes as $dial_code => $country_code)
                if (substr($phone_cleared, 0, strlen((string)$dial_code)) == $dial_code)
                {
                    $code = $dial_code;
                    $code_country = $country_code;
                    break;
                }

            if (!$code_country || !HoolahMain::get_countries() || !in_array($code_country, HoolahMain::get_countries()))
            {
                if (substr($phone, 0, 1) == '+') // country code is specified
                    throw new \Exception('We can not accept the telephone. Please use you local phone number with country code.', 9998);
                else
                {
                    if (!HoolahMain::check_country($this->extSettings->gatewayEnabledCountries(HoolahMain::get_countries()), $billing['country_id']))
                        throw new \Exception('We can not accept the billing address. We are not working in the country yet.', 9998);
                    else
                    {
                        $code = array_search($billing['country_id'], $clear_country_codes);
                        $phone = $code.$phone;
                    }
                }
            }

            $total = floatval($quote->getGrandTotal());

            //hard-coded max
            if ($billing['country_id'] == 'SG' && $total > 3000)
                throw new \Exception('The total is above max threshold in 3000.', 9998);

            if ($billing['country_id'] == 'MY' && $total > 9000)
                throw new \Exception('The total is above max threshold in 9000.', 9998);

            if ($this->extSettings->gatewayEnabledMinAmount() && $this->extSettings->gatewayEnabledMinAmount() > $total)
                throw new \Exception('The total is below min threshold in '.$this->extSettings->gatewayEnabledMinAmount().'.', 9998);

            if ($this->extSettings->gatewayEnabledMaxAmount() && $this->extSettings->gatewayEnabledMaxAmount() < $total)
                throw new \Exception('The total is above max threshold in '.$this->extSettings->gatewayEnabledMaxAmount().'.', 9998);

            if (substr($phone, 0, 1) != '+')
                $phone = '+'.$phone;

            // names
            $names = array();
            if (isset($billing['firstname'])) $names['firstname'] = $billing['firstname'];
            if (isset($billing['lastname'])) $names['lastname'] = $billing['lastname'];

            if (empty($names))
                throw new \Exception('We can not accept the billing address without first and last names.', 9998);

            $this->hlog->notice('order validation started for quote '.$quote->getEntityId(), $requestData);

            $splitted_names = null;
            if (empty($names['lastname']))
                $splitted_names = explode(' ', $names['firstname']);
            if (empty($names['firstname']))
                $splitted_names = explode(' ', $names['lastname']);

            if ($splitted_names && count($splitted_names) > 1)
            {
                $names['lastname'] = array_pop($splitted_names);
                $names['firstname'] = implode(' ', $splitted_names);
            }

            $order_data = array(
                'consumerEmail' => $billing['email'],
                'consumerFirstName' => $names['firstname'],
                //'consumerMiddleName' => '',
                'consumerLastName' => $names['lastname'],
                'consumerPhoneNumberExtension' => intval($code),
                'consumerPhoneNumber' => $phone, //preg_replace('/[^\+0-9]/', '', $billing['telephone']),
                'orderNotes' => '',//$order->get_customer_note(),
                //'merchantRef' => $quote->getEntityId(), //get_bloginfo('name'),
                'cartId' => $quote->getEntityId(),
                'currency' =>  ($this->hdata->get_mode($quote->getStoreId()) == OperationMode::MODE_LIVE) ? $quote->getQuoteCurrencyCode() : 'SGD',
                'totalAmount' => floatval($quote->getGrandTotal()),
                'originalAmount' => floatval($quote->getSubtotal()), //floatval($order->get_total()) - floatval($order->get_total_tax()) - floatval($order->get_total_shipping()) - floatval($order->get_shipping_tax()),
                'taxAmount' => floatval($quote->getShippingAddress()->getTaxAmount()), //floatval($quote->getShippingAddress()->getSubtotalInclTax()) - floatval($quote->getSubtotal()),
                'shippingAmount' => floatval($quote->getShippingAddress()->getShippingAmount()) + floatval($quote->getShippingAddress()->getShippingTaxAmount()),
                'orderType' => 'ONLINE',
                'shippingMethod' => 'NORMAL',
                'shippingAddress' => array(
                    'line1' => $shipping['street'],
                    'city' => $shipping['city'],
                    'postcode' => $shipping['postcode'],
                    'countryCode' => $shipping['country_id']
                ),
                'billingAddress' => array(
                    'line1' => $billing['street'],
                    'city' => $billing['city'],
                    'postcode' => $billing['postcode'],
                    'countryCode' => $billing['country_id']
                ),
                'items' => array(),
                'callbackUrl' => $this->_url->getUrl('hoolah/gateway/callback/').'?quote_id='.$quote->getEntityId(),
                'closeUrl' => $this->_url->getUrl('hoolah/gateway/close/').'?quote_id='.$quote->getEntityId(),
                'returnToShopUrl' => $this->_url->getUrl('hoolah/gateway/thankyou/').'?quote_id='.$quote->getEntityId()
            );

            if (empty($order_data['billingAddress']['city']) && $order_data['billingAddress']['countryCode'] == 'SG')
                $order_data['billingAddress']['city'] = 'Singapore';
            if (empty($order_data['shippingAddress']['city']) && $order_data['shippingAddress']['countryCode'] == 'SG')
                $order_data['shippingAddress']['city'] = 'Singapore';

            $field_city_title = 'Billing Town / City';
            if ($this->hdata->get_billing_city_field_title())
                $field_city_title = $this->hdata->get_billing_city_field_title();
            if (empty($order_data['billingAddress']['city']) && $order_data['billingAddress']['countryCode'] == 'MY')
                throw new \Exception($field_city_title.' is a required field', 9997);

            if (!empty($shipping['region']))
                $order_data['shippingAddress']['state'] = $shipping['region'];
            if (!empty($billing['region']))
                $order_data['billingAddress']['state'] = $billing['region'];

            if ($order_data['billingAddress']['countryCode'] == 'HK')
            {
                $order_data['shippingAddress']['district'] = $shipping['city'];
                $order_data['billingAddress']['district'] = $billing['city'];
            }

            // no empty shipping address
            if (!$order_data['shippingAddress']['line1'] &&
                !$order_data['shippingAddress']['city'] &&
                !$order_data['shippingAddress']['postcode'] &&
                !$order_data['shippingAddress']['countryCode'])
                $order_data['shippingAddress'] = $order_data['billingAddress'];

            $user_title = 'Other'; //Hoolah_Fields_UserTitle::get_order_value($order);
            if ($user_title)
                $order_data['consumerTitle'] = $user_title;

            $coupons = $quote->getCouponCode();
            if ($coupons)
                $order_data['voucherCode'] = $coupons;

            $shipping_method = $quote->getShippingAddress()->getShippingMethod();
            if ($shipping_method) {
                switch ($shipping_method) {
                    case 'freeshipping_freeshipping':
                        $order_data['orderType'] = 'ONLINE';
                        $order_data['shippingMethod'] = 'FREE';
                        break;
                    case 'flatrate_flatrate':
                        $order_data['orderType'] = 'ONLINE';
                        $order_data['shippingMethod'] = 'NORMAL';
                        break;
                    //case 'local_pickup': // ?!
                    //    $order_data['orderType'] = 'ONLINE';
                    //    $order_data['shippingMethod'] = 'PICKUP';
                    //    $order_data['shippingAddress'] = array(
                    //        'line1' => get_option( 'woocommerce_store_address' ),
                    //        'city' => get_option( 'woocommerce_store_city' ),
                    //        'postcode' => get_option( 'woocommerce_store_postcode' ),
                    //        'countryCode' => get_option( 'woocommerce_default_country' )
                    //    );
                    //    break;
                }
            }
            //$originalAmount = 0;
            //$totalAmount = 0;
            //$discountAmount = 0;
            foreach ($quote->getAllVisibleItems() as $item)
            {
                $product = $item->getProduct();
                //if ($item->get_variation_id())
                //    $product = wc_get_product($item->get_variation_id());
                //else
                //    $product = wc_get_product($item->get_product_id());

                $product_name = $product->getName();
                //customize here
                $product_description = $product->getDescription() ? $product->getDescription() : '';
                //end of customize
                $product_sku = $product->getSku();

                $product_regular_price = floatval($item->getOriginalPrice());
                $product_price = floatval($item->getPrice());
                $product_tax = floatval($item->getBaseRowTotalInclTax() - $item->getBaseRowTotal())/floatval($item->getQty()); // $item->getTaxAmount()

                //$originalAmount += floatval($product->get_regular_price());
                //$totalAmount += floatval($product->get_price());

                $order_item_data = array(
                    'name' => $product_name,
                    'description' => HoolahMain::remove_emoji($product_description), // wp_strip_all_tags
                    'sku' => $product_sku,
                    //'ean' => 'eandfsdf',
                    'quantity' => floatval($item->getQty()),
                    'price' => $product_price + $product_tax,
                    'originalPrice' => $product_price,
                    'taxAmount' => $product_tax
                );

                if ($item->getDiscountAmount())
                {
                    $order_item_data['discount'] = floatval($item->getDiscountAmount())/floatval($item->getQty());
                    //$discountAmount += floatval($item->getDiscountAmount());
                }

                if (!$order_item_data['sku'])
                    $order_item_data['sku'] = 'NA';

                $this->galleryReadHandler->execute($product);

                $images = $product->getMediaGalleryImages()->toArray();
                if (@$images['totalRecords'])
                    foreach ($images['items'] as $image)
                        if ($image && $image['url'])
                            $order_item_data['images'][] = array(
                                'imageLocation' => $image['url']
                            );

                $order_data['items'][] = $order_item_data;
            }

            if ($quote->getSubtotal() - $quote->getSubtotalWithDiscount() > 0)
                $order_data['discount'] = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
            //if ($discountAmount)
            //    $order_data['discount'] = $discountAmount;
            //if (floatval($quote->getDiscountAmount()))
            //    $order_data['discount'] = floatval($quote->getDiscountAmount());

            // find delivery and use original price
            //$order_data['originalAmount'] = $order_data['totalAmount'] - $totalAmount + $originalAmount;

            //var_dump($order_data['totalAmount'], $order_data['originalAmount'] - @$order_data['discount'] + $order_data['taxAmount'] + $order_data['shippingAmount'], $order_data);

            if ($debug)
            {
                var_dump('$order_data', $order_data);
                var_dump('$billing', $billing);
                var_dump('$shipping', $shipping);
                die();
            }

            $this->hlog->notice('hoolah new order started for quote '.$quote->getEntityId(), $order_data);

            $response = $api->merchant_order_initiate($order_data);
            if (!HoolahAPI::is_201($response))
            {
                $this->hlog->notice('... was FAILED', $response['body']);

                throw new \Exception('Order initiation error ('.HoolahAPI::get_message($response).')', 9999);
            }

            //$order->addStatusHistoryComment('Order context token is '.$response['body']['orderContextToken']);
            $quote->setHoolahOrderContextToken($response['body']['orderContextToken']);
            $quote->setHoolahOrderRef($response['body']['orderUuid']);
            $quote->setHoolahUpdateAttempts(0);
            //$order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
            $quote->save();

            $this->hlog->notice('... was SUCCEEDED', $response['body']);
            $this->hlog->notice('data from the quote hoolah_order_ref = '.$quote->getHoolahOrderRef().', hoolah_order_context_token = '.$quote->getHoolahOrderContextToken());

            if ($this->hdata->getOrderMode() == OrderMode::MODE_ORDER_PAYMENT)
            {
                if (!$this->horder->createOrder($quote)) {
                    //add customize here
                    $jsonf = $this->resultJsonFactory->create();
                    //end of customize
                    return $jsonf->setData(array(
                        'success' => false,
                        'message' => 'order creation error'
                    ));
                }
            }

            $result = array(
                'success' => true,
                'redirect' => sprintf($this->hdata->get_hoolah_jsurl(@$order_data['billingAddress']['countryCode']),
                    $response['body']['orderContextToken'],
                    $this->hdata->getVersion()
                )
            );
        }
        catch (\Throwable $e)
        {
            $message = $e->getMessage();

            if ($e->getCode() == 9999)
                $message .= '. Please use different payment method.';

            $result = array(
                'success' => false,
                'message' => $message
            );
        }

        $jsonf = $this->resultJsonFactory->create();

        if (!$result['success'])
            $jsonf->setHttpResponseCode(400);

        return $jsonf->setData($result);
    }

    /**
     * Prevent update shippingMethod again
     *
     * @param $object
     * @param $data
     * @return void
     */
    protected function updateAddress($object, $data)
    {
        foreach ($data as $propertyName => $setterValue) {
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            $setterName = 'set'.$camelCaseProperty;

            if (in_array($setterName, array('setQuote', 'setExtensionAttributes', 'setShippingMethod'))) // ignore it
                continue;

            try
            {
                if (is_callable(array($object, $setterName)))
                    $object->{$setterName}($setterValue);
            }
            catch (\Error $e)
            {
                $this->hlog->notice('hoolah updateAddress error - '.$e->getMessage().' - '.$e->getTraceAsString());
            }
        }

        $object->save();
    }
}
