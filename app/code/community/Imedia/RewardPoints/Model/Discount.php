<?php
class Imedia_RewardPoints_Model_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {
 
     protected $_code = 'fee';
 
    public function collect(Mage_Sales_Model_Quote_Address $address) {
			parent::collect($address);

			$this->_setAmount(0);
			$this->_setBaseAmount(0);

			$items = $this->_getAddressItems($address);
			if (!count($items)) {
				return $this; //this makes only address type shipping to come through
			}

			$quote = $address->getQuote();
	
            if ($address->getData('address_type') == 'billing')
				
				
				$exist_amount = $quote->getFeeAmount();
				$fee = Mage::getSingleton('customer/session')->getDisc(); //your discount
				$discount = $fee - $exist_amount ;
 
                $address->setFeeAmount($discount);
                $address->setBaseFeeAmount($discount);
				
				$grandTotal = $address->getGrandTotal();
                $baseGrandTotal = $address->getBaseGrandTotal();
				$quote->setFeeAmount($discount);
         				 
                $address->setGrandTotal($grandTotal - $address->getFeeAmount());
                $address->setBaseGrandTotal($baseGrandTotal - $address->getBaseFeeAmount());				
                
        return $this;
    }
	
	public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
	    $rewards1 = $address->getFeeAmount();
        if ($rewards1!=0) {
            $title = Mage::helper('sales')->__('Reward Points Discount');
            $code = $address->getCouponCode();
            if (strlen($code)) {
                $title = Mage::helper('sales')->__('Reward Points Discount (%s)', $code);
            }
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>$title,
                'value'=>$rewards1
            ));
			
        }
        return $this;
    }
 
}