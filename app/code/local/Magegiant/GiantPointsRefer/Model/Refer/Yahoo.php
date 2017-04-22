<?php
class Magegiant_GiantPointsRefer_Model_Refer_Yahoo
{
	public function __construct(){
		try {
			require Mage::getBaseDir('lib').DS.'Yahoo'.DS.'Yahoo.inc';
		} catch (Exception $e) {
		}
		error_reporting(E_ALL | E_NOTICE);
		ini_set('display_errors', true);

		YahooLogger::setDebug(true);
		YahooLogger::setDebugDestination('LOG');
		
		ini_set('session.save_handler', 'files');
		session_save_path('/tmp/');
		session_start();
		
		if(array_key_exists("logout", $_GET)){
			YahooSession::clearSession();
		}
	}

	protected function _getAppId(){
		return Mage::getStoreConfig('giantpoints/referral_system_configuration/yahoo_app_id');
	}
	
	protected function _getConsumerKey(){
		return Mage::getStoreConfig('giantpoints/referral_system_configuration/yahoo_consumer_key');
	}
	
	protected function _getConsumerSecret(){
		return Mage::getStoreConfig('giantpoints/referral_system_configuration/yahoo_consumer_secret');
	}
	
	public function hasSession(){
		return YahooSession::hasSession($this->_getConsumerKey(), $this->_getConsumerSecret(), $this->_getAppId());
	}
	
	public function getAuthUrl(){
		return YahooSession::createAuthorizationUrl($this->_getConsumerKey(), $this->_getConsumerSecret());
	}
	
	public function getSession(){
		return YahooSession::requireSession($this->_getConsumerKey(), $this->_getConsumerSecret(), $this->_getAppId());
	}
}