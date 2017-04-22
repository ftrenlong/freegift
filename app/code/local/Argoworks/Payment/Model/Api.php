<?php 
class Argoworks_Payment_Model_Api extends Mage_Api_Model_Resource_Abstract{
	
	public function listPaymentMethods(){
		try {
			$methods = array();
			$payments = Mage::getSingleton('payment/config')->getActiveMethods();
			foreach ($payments as $paymentCode => $paymentModel) {
				$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
				$methods[$paymentCode] = $paymentTitle;
			}
			return $methods;
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
			// We cannot know all the possible exceptions,
			// so let's try to catch the ones that extend Mage_Core_Exception
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
	}
}
?>