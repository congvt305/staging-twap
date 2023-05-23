<?php //m2.3
    namespace Hoolah\Hoolah\Helper\HTTP;
    
    class ZendClientWOMCA23 extends \Magento\Framework\HTTP\ZendClient
    {
        protected function _trySetCurlAdapter()
        {
            return $this;
        }
    }