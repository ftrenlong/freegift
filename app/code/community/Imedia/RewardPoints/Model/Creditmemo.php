<?php
class Imedia_RewardPoints_Model_Creditmemo extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {
 
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        /*$order = $creditmemo->getOrder();
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $order->getFeeAmount());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $order->getBaseFeeAmount());
		$creditmemo->setFeeAmount($order->getFeeAmount());
		$creditmemo->setBaseFeeAmount($order->getBaseFeeAmount());*/
		
		$creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $creditmemo->getFeeAmount());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $creditmemo->getBaseFeeAmount());
		$creditmemo->setFeeAmount($creditmemo->getFeeAmount());
		$creditmemo->setBaseFeeAmount($creditmemo->getBaseFeeAmount());

        return $this;
    }
 
}