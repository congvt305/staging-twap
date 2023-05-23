<?php //m2.2
    namespace Hoolah\Hoolah\Helper\HTTP;
    
    class ZendClientWOMCA22 extends \Hoolah\Hoolah\Helper\HTTP\ZendClient
    {
        protected function _trySetCurlAdapter()
        {
            return $this;
        }
    }