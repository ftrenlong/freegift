<?php
class Imedia_RewardPoints_Block_Adminhtml_Sales_Order extends Mage_Sales_Block_Order_Totals
{
   protected function _initTotals() {

     parent::_initTotals();
        $amt = $this->getSource()->getFeeAmount();
        $baseAmt = $this->getSource()->getBaseFeeAmount();
        if ($amt != 0) {
            $this->addTotal(new Varien_Object(array(
                        'code' => 'Fee',
                        'value' => $amt,
                        'base_value' => $baseAmt,
                        'label' => 'Reward points discount',
                    )), 'discount');
        }
        return $this;
    }	
}