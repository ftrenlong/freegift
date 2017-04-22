<?php
class Imedia_RewardPoints_Block_Adminhtml_Sales_Creditmemo extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
protected function _initTotals() {
        parent::_initTotals();
		$amt = $this->getSource()->getFeeAmount();
		//Mage::log('creditmemo.php: '.$amt);
        $baseAmt = $this->getSource()->getBaseFeeAmount();
        if ($amt != 0) {
 
            $this->addTotal(new Varien_Object(array(
                        'code' => 'Discount',
                        'value' => $amt,
                        'base_value' => $baseAmt,
                        'label' => 'Rewards points discount',
                    )), 'discount');
        }
        return $this;
    }					
	
}
