<?php
    namespace Hoolah\Hoolah\Helper;

    use \Magento\Store\Model\ScopeInterface;
    
    class Log extends \Magento\Framework\App\Helper\AbstractHelper
    {
        // protected
        protected $hoolahLogFactory = null;
        
        protected $thread = 0;
        protected $sequence = 0;
        
        protected function prepareDetails($details)
        {
            if ($details != null && !is_string($details))
                $details = json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            return $details;
        }
        
        protected function writeInDB($description, $details = null)
        {
            if (!$this->get_disable_db_log())
            {
                $this->sequence++;
                
                $model = $this->hoolahLogFactory->create();
                $model->addData([
                    'ip' => @$_SERVER['SERVER_ADDR'],
                    'thread' => $this->thread,
                    'sequence' => $this->sequence,
                    'description' => $description,
                    'details' => $details
                ]);
                $model->save();
            }
        }
        
        // public
        public function __construct(
            \Magento\Framework\App\Helper\Context $context,
            \Hoolah\Hoolah\Model\HoolahLogFactory $hoolahLogFactory
        )
        {
            parent::__construct($context);
            
            $this->hoolahLogFactory = $hoolahLogFactory;
            $this->thread = rand(1, 1000);
        }
        
        public function notice($description, $details = null)
        {
            $details = $this->prepareDetails($details);
            
            $this->_logger->notice('hoolah thread '.@$_SERVER['SERVER_ADDR'].'/'.$this->thread.': '.$description.' ['.$details.']');
            
            $this->writeInDB($description, $details);
        }
        
        public function error($description, $details = null)
        {
            $details = $this->prepareDetails($details);
            
            $this->_logger->error('hoolah thread '.@$_SERVER['SERVER_ADDR'].'/'.$this->thread.': '.$description.' ['.$details.']');
            
            $this->writeInDB($description, $details);
        }

        public function get_disable_db_log($store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/disable_db_log',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
    }