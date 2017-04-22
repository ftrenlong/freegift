<?php
class Imedia_RewardPoints_Model_Invoice extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
 
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
                         
                $invoice->setGrandTotal($invoice->getGrandTotal() - $invoice->getFeeAmount());
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $invoice->getBaseFeeAmount());
				
				$invoice->setFeeAmount($invoice->getFeeAmount());
				$invoice->setBaseFeeAmount($invoice->getBaseFeeAmount());
				
				//Mage::log('invoice11: ');
        return $this;
		
    }
 
}
