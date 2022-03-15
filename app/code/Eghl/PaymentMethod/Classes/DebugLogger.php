<?php
namespace Eghl\PaymentMethod\Classes;

use \Magento\Framework\App\ObjectManager;

class DebugLogger{
	
	const PATH_RELATIVE_ROOT = '/var/log/eghl_Logs/Debug/'; //Path relative to root
	private static $LogPath;
	private static $OrderNumber;
	
	// Hold an instance of the class
    private static $instance;
	
	private static $fhandle;
	private static $fname;
	
	private static $magento_logger;
	private static $helper;
	private static $eghl_config = array(
		'active'	=>	'',
		'debug'	=>	'',
		'fail_payment_email'	=>	'',
		'payment_url'	=>	'',
		'order_status'	=>	'',
		'payment_success_status'	=>	'',
		'payment_cancel_status'	=>	'',
		'payment_fail_status'	=>	'',
		'payment_pending_status'	=>	'',
		'pay_method'	=>	'',
		'currency_type'	=>	'',
		'mid'	=>	'',
		'page_timeout'	=>	'',
		'title'	=>	'',
		'customer_consent'	=>	''
	);
	
	private function __construct(){
		$objectManager = ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		self::$LogPath  =  $directory->getRoot().self::PATH_RELATIVE_ROOT;
		
		self::$helper = $objectManager->get('\Eghl\PaymentMethod\Helper\Data');
		
		self::$magento_logger = $objectManager->get('\Psr\Log\LoggerInterface');
		
		if(!file_exists(self::$LogPath)){
			if (!mkdir(self::$LogPath, 0777, true)) {
				die('Failed to create folders "'.self::$LogPath.'"');
			}
		}
		
		try{
			self::load_plugin_settings();
			if(self::$eghl_config['debug']==1){
				self::$fname = 'eghlDebug_'.date("Y-m-d").'.log';
				self::$fhandle = fopen(self::$LogPath.self::$fname, "a+");
				self::writeArray(self::$eghl_config, 'eGHL plugin config');
			}
		}
		catch(\Exception $e) {
			self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger Exception: unable to Open file [".self::$fname."] -> ".$e->getMessage());
		}
	}
	
	private function load_plugin_settings(){
		foreach(self::$eghl_config as $ind=>$val){
			self::$eghl_config[$ind] = self::$helper->getGeneralConfig($ind);
		}
	}
	
	public static function writeString($string){
		try{
			if(self::$eghl_config['debug']==1){
				$written = false;
				$timerStart = time();
				while(!$written){
					$timerNow = time();
					// will not wait for lock more than 10 secs
					if(($timerNow-$timerStart) > 10){
						self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger (writeString): -> Could not Acquire lock");
						break;
					}
					if(flock(self::$fhandle, LOCK_EX)){ // acquire an exclusive lock
						fwrite(self::$fhandle, date('d/m/Y, H:i:s').' '.$string.PHP_EOL);
						flock(self::$fhandle, LOCK_UN);    // release the lock
						$written = true;
					}
				}				
			}
		}
		catch(\Exception $e) {
			self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger Exception (writeString): -> ".$e->getMessage());
		}	
	}

	public static function hasString($string){
		try{
			if( strpos(file_get_contents(self::$LogPath.self::$fname),$string) !== false) {
				return true;
			}
			else{
				return false;
			}
		}
		catch(\Exception $e) {
			self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger Exception (writeString): -> ".$e->getMessage());
		}	
	}
	
	public static function writeArray($arr = array(),$string = ''){
		try{
			if(self::$eghl_config['debug']==1){
				$written = false;
				$timerStart = time();
				while(!$written){
					$timerNow = time();
					// will not wait for lock more than 10 secs
					if(($timerNow-$timerStart) > 10){
						self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger (writeArray): -> Could not Acquire lock");
						break;
					}
					if(flock(self::$fhandle, LOCK_EX)){ // acquire an exclusive lock
						fwrite(self::$fhandle, date('d/m/Y, H:i:s').' '.$string.' -> '.json_encode($arr,1).PHP_EOL);
						flock(self::$fhandle, LOCK_UN);    // release the lock
						$written = true;
					}
				}
			}
		}
		catch(\Exception $e) {
			self::$magento_logger->critical("Eghl\PaymentMethod\Classes\DebugLogger Exception (writeArray): -> ".$e->getMessage());
		}	
	}
	
	// The singleton method
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DebugLogger();
        }
        return self::$instance;
    }
	
	public function __destruct(){
		if(is_resource(self::$fhandle)){
			fclose(self::$fhandle);
		}
	}
}

?>