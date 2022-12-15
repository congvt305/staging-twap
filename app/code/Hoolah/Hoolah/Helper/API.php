<?php
    namespace Hoolah\Hoolah\Helper;
    
    class API
    {
        // static
        public static function is_200($response)
        {
            return @$response['response']['code'] == 200;
        }
        public static function is_201($response)
        {
            return @$response['response']['code'] == 201;
        }
        public static function is_202($response)
        {
            return @$response['response']['code'] == 202;
        }
        
        public static function get_message($response)
        {
            $result = null;
            
            if (@$response['body']['message'])
                $result = $response['body']['message'];
            elseif (@$response['body']['errorMessages'])
                $result = $response['body']['errorMessages'];
            
            if (is_array($result))
                $result = implode(', ', $result);
            
            return $result;
        }
        
        public static function get_client($avoid_magento_curl_adapter = false)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            
            if ($avoid_magento_curl_adapter)
            {
                if (version_compare($productMetadata->getVersion(), '2.3', '>='))
                    return new \Hoolah\Hoolah\Helper\HTTP\ZendClientWOMCA23(); //m2.3
                else
                    return new \Hoolah\Hoolah\Helper\HTTP\ZendClientWOMCA22(); //m2.2
            }
            else
            {
                if (version_compare($productMetadata->getVersion(), '2.3', '>='))
                    return new \Magento\Framework\HTTP\ZendClient(); //m2.3
                else
                    return new \Hoolah\Hoolah\Helper\HTTP\ZendClient(); //m2.2
            }
        }
        
        public static function url_exists($url)
        {
            $client = self::get_client();
            $client->setUri($url);
            $client->setMethod('HEAD');
            $result = $client->request();
            
            $result = array(
                'response' => array(
                    'code' => $result->getStatus()
                )
            );
            
            //$result = wp_remote_head($url);
            
            return self::is_200($result);
        }
        
        public static function get_content($url)
        {
            $client = self::get_client();
            $client->setUri($url);
            $result = $client->request();
            
            $result = array(
                'body' => $result->getBody(),
                'response' => array(
                    'code' => $result->getStatus()
                ),
            );
            
            return $result;
        }
        
        // private
        private $fstr_url = null;
        private $fstr_merchantID = null;
        private $fstr_merchantSecret = null;
        
        private $fstr_token = null;
        
        private function request($path, $data = null, $method = null)
        {
            $result = null;
            
            $client = self::get_client($method == 'PATCH');
            $client->setHeaders('Content-Type', 'application/json');
            $client->setHeaders('Accept', 'application/json');
            
            if ($this->fstr_token)
                $client->setHeaders('Authorization', 'Bearer '.$this->fstr_token);
            
            //$args = array(
            //    'headers' => array(
            //        'Content-Type' => 'application/json',
            //        'Accept' => 'application/json'
            //    )
            //);
            //if ($this->fstr_token)
            //    $args['headers']['Authorization'] = 'Bearer '.$this->fstr_token;
            
            if ($data !== null)
            {
                if (!$method)
                    $method = 'POST';
                //$args['body'] = json_encode($data);
                
                $client->setRawData(json_encode($data));
                
                //$result = wp_remote_post(
                //    $this->fstr_url.$path,
                //    $args
                //);
            }
            else
            {
                if (!$method)
                    $method = 'GET';

                //$result = wp_remote_get(
                //    $this->fstr_url.$path,
                //    $args
                //);
            }

            $client->setMethod($method);
            $client->setUri($this->fstr_url.$path);
            
            $result = $client->request();
            
            //if (is_wp_error($result))
            //    throw new Exception($result->get_error_message(), 9999);
            
            //$result['body'] = json_decode($result['body'], true);
            
            $result = array(
                'response' => array(
                    'code' => $result->getStatus()
                ),
                'body' => json_decode($result->getBody(), true)
            );
            
            return $result;
        }
        
        private function check_token()
        {
            if (!$this->fstr_token)
            {
                $result = $this->merchant_auth_login();
                if (!$this->fstr_token)
                    return $result;
            }
            
            return true;
        }
        
        // public
        public function __construct($merchant_id, $merchant_secret, $url)
        {
            $this->fstr_url = $url;
            $this->fstr_merchantID = $merchant_id;
            $this->fstr_merchantSecret = $merchant_secret;
        }
        
        public function merchant_auth_login()
        {
            $result = $this->request('/merchant/auth/login', array(
                'username' => $this->fstr_merchantID,
                'password' => $this->fstr_merchantSecret
            ));
            
            if (self::is_200($result))
                $this->fstr_token = $result['body']['token'];
            
            return $result;
        }
        
        public function merchant_order_get($uuid)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/'.$uuid);
            
            return $result;
        }
        
        public function merchant_order_initiate($order)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/initiate', $order);
            
            return $result;
        }
        
        public function merchant_order_full_refund($uuid, $description = null)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/'.$uuid.'/full-refund', array(
                'description' => $description
            ));
            
            return $result;
        }
        
        public function merchant_order_partial_refund($uuid, $amount, $description = null, $items = null)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/'.$uuid.'/partial-refund', array(
                'amount' => $amount,
                'items' => $items,
                'description' => $description
            ));
            
            return $result;
        }
        
        public function merchant_order_finalize($uuid, $quote_id, $order_id, $email)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/finalize/'.$uuid, array(
                'cart_id' => $quote_id,
                'email' => $email,
                'created_at' => date('c'),
                'merchant_order_id' => $order_id,
                'status' => 'paid',
                'order_uuid' => $uuid
            ));
            
            return $result;
        }

        public function merchant_order_patch_order_id($uuid, $order_id)
        {
            $result = $this->check_token();
            if ($result !== true)
                return $result;
            
            $result = $this->request('/merchant/order/'.$uuid, array(
                'merchantOrderId' => $order_id,
            ), 'PATCH');
            
            return $result;
        }
        
        public function send_logs($data, $url)
        {
            $client = self::get_client();
            
            $client->setAuth($this->fstr_merchantID, $this->fstr_merchantSecret, \Magento\Framework\HTTP\ZendClient::AUTH_BASIC);
            
            $client->setMethod('POST');
            $client->setUri($url);
            
            $offset = 0;
            $length = 30000000;
            while ($offset < strlen($data))
            {
                $client->setRawData(substr($data, $offset, $length));
                
                $result = $client->request();
                
                $result = array(
                    'response' => array(
                        'code' => $result->getStatus()
                    ),
                    'body' => $result->getBody()
                );
                
                if (!self::is_200($result))
                    return $result;
                
                $offset += $length;
            }
            
            return $result;
        }
    }