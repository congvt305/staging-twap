<?php
    namespace Hoolah\Hoolah\Controller\Adminhtml\System\Config;
    
    use \Exception;
    use \Throwable;

    use \Magento\Backend\App\Action;
    use \Magento\Backend\App\Action\Context;
    use \Magento\Framework\Controller\Result\JsonFactory;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\API as HoolahAPI;
    
    use \Hoolah\Hoolah\Model\Config\Source\OperationMode as OperationMode;
     
    class SendLogs extends Action
    {
        public static function logsPath()
        {
            return array(
                BP.'/var/log/system.log',
                BP.'/var/log/debug.log'
            );
        }
        
        public static function getLines($filepath, $timeScope, $linesScope)
        {
            $timeScope = $timeScope * 24 * 60 * 60;
            $now = time();
            
            $f = @fopen($filepath, "r");
            if ($f === false) return false;
            
            $start = 0;
            $end = filesize($filepath);
            
            while ($end - $start > 1000000)
            {
                fseek($f, intval(($end + $start) / 2));
                
                $time = false;
                while (!$time && ($line = fgets($f, 4096)))
                    $time = strtotime(substr($line, 1, strpos($line, ']') - 1));
                
                if (!$time || $now - $time <= $timeScope)
                    $end = intval(($end + $start) / 2);
                else
                    $start = intval(($end + $start) / 2);
            }
            
            $output = '';
            $chunk = '';
            
            $get = false;
            fseek($f, $start);
            while ($line = fgets($f, 4096))
            {
                $time = strtotime(substr($line, 1, strpos($line, ']') - 1));
                if ($time)
                {
                    $get = $now - $time <= $timeScope;
                    
                    if ($get && $linesScope == 'hoolah')
                        $get = strpos($line, 'hoolah') !== false || strpos($line, '... was') !== false;
                }
                
                if ($get)
                    $output .= $line;
            }
            
            fclose($f);
            return trim($output);
        }
        
        protected $resultJsonFactory;
        
        protected $searchCriteriaBuilder;
        protected $sortOrderBuilder;
        
        protected $quoteRepository;
        protected $orderRepository;
        protected $invoiceRepository;
        protected $transactionRepository;
        
        protected $hlogsRepository;
        
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            Context $context,
            JsonFactory $resultJsonFactory,
            \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
            \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
            \Magento\Quote\Model\QuoteRepository $quoteRepository,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
            \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
            \Hoolah\Hoolah\Model\HoolahLogRepository $hlogsRepository
        )
        {
            $this->resultJsonFactory = $resultJsonFactory;
            
            $this->searchCriteriaBuilder = $searchCriteriaBuilder;
            $this->sortOrderBuilder = $sortOrderBuilder;
            
            $this->quoteRepository = $quoteRepository;
            $this->orderRepository = $orderRepository;
            $this->invoiceRepository = $invoiceRepository;
            $this->transactionRepository = $transactionRepository;
            
            $this->hlogsRepository = $hlogsRepository;

            parent::__construct($context);
        }
     
        /**
         * Collect relations data
         *
         * @return \Magento\Framework\Controller\Result\Json
         */
        public function execute()
        {
            HoolahMain::load_configs();
            
            $result = array(
                'ok' => false,
                'error' => [
                    'response' => [
                        'code' => 500
                    ],
                    'body' => 'Unknown error'
                ]
            );
            
            try
            {
                $relates = @$_POST['logs_relates'].' '.@$_POST['logs_details'];
                
                $api = null;
                $logs_url = null;
                $credentials = null;
                if ($_POST['mode'] == OperationMode::MODE_TEST)
                {
                    $credentials = [@trim($_POST['merchant_id']), @trim($_POST['merchant_secret_test']), HOOLAH_API_HOST_SANDBOX];
                    $logs_url = sprintf(HOOLAH_LOG_DEMO, trim($_POST['merchant_cdn_id']), urlencode($relates));
                }
                else if ($_POST['mode'] == OperationMode::MODE_LIVE)
                {
                    $credentials = [@trim($_POST['merchant_id']), @trim($_POST['merchant_secret']), HOOLAH_API_HOST_SANDBOX];
                    $logs_url = sprintf(HOOLAH_LOG_PROD, trim($_POST['merchant_cdn_id']), urlencode($relates));
                }
                
                if (!$credentials)
                    throw new Exception('Unknown mode');
                
                if (empty($credentials[0]) || empty($credentials[1]) || empty($credentials[2]))
                    throw new Exception('Merchant credentials are empty');
                
                $api = new HoolahAPI($credentials[0], $credentials[1], $credentials[2]);
                
                if ($api)
                {
                    // db logs
                    $data = "DB logs\n------------------\n";
                    try
                    {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('created_at', date('Y-m-d', time() - @$_POST['logs_time_scope'] * 60*60*24), 'gt')
                            ->setSortOrders([
                                $this->sortOrderBuilder->setField('created_at')->setDirection('ASC')->create(),
                                $this->sortOrderBuilder->setField('sequence')->setDirection('ASC')->create()
                            ])
                            ->create();
                        
                        $hlogs = $this->hlogsRepository->getList($searchCriteria);
                        if ($hlogs->getItems())
                        {
                            $items = $hlogs->getItems();
                            
                            $data .= "Count: ".count($items)."\n";
                            foreach ($items as $hlog)
                            {
                                $data .= $hlog->getCreatedAt().' ['.$hlog->getIp().'/'.$hlog->getThread().']: '.$hlog->getDescription()."\n";
                                
                                if ($hlog->getDetails())
                                    $data .= $hlog->getDetails()."\n";
                            }
                        }
                        else
                            $data .= "<empty>\n";
                    }
                    catch (\Throwable $e)
                    {
                        $data .= "Exception: ".$e->getMessage();
                    }
                    
                    $result['db_logs'] = $api->send_logs($data, $logs_url);
                    if (!HoolahAPI::is_200($result['db_logs']))
                        $result['error'] = $result['db_logs'];
                    $result['ok'] = HoolahAPI::is_200($result['db_logs']);
                    
                    // db data
                    $data = "Quotes\n------------------\n";
                    try
                    {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('hoolah_order_context_token', null, 'notnull')
                            ->addFilter('updated_at', date('Y-m-d', time() - @$_POST['logs_time_scope'] * 60*60*24), 'gt')
                            ->create();
                        $quotes = $this->quoteRepository->getList($searchCriteria);
                        if ($quotes->getTotalCount())
                        {
                            $data .= "Count: ".$quotes->getTotalCount()."\n";
                            foreach ($quotes->getItems() as $quote)
                                $data .= json_encode($quote->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
                        }
                        else
                            $data .= "<empty>\n";
                    }
                    catch (\Throwable $e)
                    {
                        $data .= "Exception: ".$e->getMessage();
                    }
    
                    $data .= "Orders\n------------------\n";
                    try
                    {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('hoolah_order_context_token', null, 'notnull')
                            ->addFilter('updated_at', date('Y-m-d', time() - @$_POST['logs_time_scope'] * 60*60*24), 'gt')
                            ->create();
                        $orders = $this->orderRepository->getList($searchCriteria);
                        if ($orders->getTotalCount())
                        {
                            $data .= "Count: ".$orders->getTotalCount()."\n";
                            foreach ($orders->getItems() as $order)
                                $data .= json_encode($order->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
                        }
                        else
                            $data .= "<empty>\n";
                    }
                    catch (\Throwable $e)
                    {
                        $data .= "Exception: ".$e->getMessage();
                    }
    
                    $data .= "Invoices\n------------------\n";
                    try
                    {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('transaction_id', 'hoolah_%', 'like')
                            ->addFilter('updated_at', date('Y-m-d', time() - @$_POST['logs_time_scope'] * 60*60*24), 'gt')
                            ->create();
                        $ivoices = $this->invoiceRepository->getList($searchCriteria);
                        if ($ivoices->getTotalCount())
                        {
                            $data .= "Count: ".$ivoices->getTotalCount()."\n";
                            foreach ($ivoices->getItems() as $ivoice)
                                $data .= json_encode($ivoice->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
                        }
                        else
                            $data .= "<empty>\n";
                    }
                    catch (\Throwable $e)
                    {
                        $data .= "Exception: ".$e->getMessage();
                    }
    
                    $data .= "Transactions\n------------------\n";
                    try
                    {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('txn_id', 'hoolah_%', 'like')
                            ->addFilter('created_at', date('Y-m-d', time() - @$_POST['logs_time_scope'] * 60*60*24), 'gt')
                            ->create();
                        $transactions = $this->transactionRepository->getList($searchCriteria);
                        if ($transactions->getTotalCount())
                        {
                            $data .= "Count: ".$transactions->getTotalCount()."\n";
                            foreach ($transactions->getItems() as $transaction)
                                $data .= json_encode($transaction->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
                        }
                        else
                            $data .= "<empty>\n";
                    }
                    catch (\Throwable $e)
                    {
                        $data .= "Exception: ".$e->getMessage();
                    }
                    
                    $result['db_data'] = $api->send_logs($data, $logs_url);
                    if (!HoolahAPI::is_200($result['db_data']))
                        $result['error'] = $result['db_data'];
                    $result['ok'] = $result['ok'] && HoolahAPI::is_200($result['db_data']);
        
                    // file logs
                    $data = "File logs\n------------------\n";
    
                    foreach (self::logsPath() as $logPath)
                        $data .= self::getLines($logPath, @$_POST['logs_time_scope'], @$_POST['logs_lines_scope']);
                    
                    $result['file_logs'] = $api->send_logs($data, $logs_url);
                    if (!HoolahAPI::is_200($result['file_logs']))
                        $result['error'] = $result['file_logs'];
                    $result['ok'] = $result['ok'] && HoolahAPI::is_200($result['file_logs']);
                    
                    if ($result['ok'])
                        unset($result['error']);
                }
            }
            catch (\Throwable $e)
            {
                $result['ok'] = false;
                $result['error'] = [
                    'response' => [
                        'code' => 500
                    ],
                    'body' => $e->getMessage()
                ];
            }
            
            $jsonf = $this->resultJsonFactory->create();
            
            return $jsonf->setData($result);
        }
    }