<?php
class Imedia_RewardPoints_Block_Adminhtml_Renderer_CustomerName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
                if ($row->getData('firstname') != NULL || $row->getData('lastname') != NULL) {
			        $firstName = $row->getData('firstname');
	                $lastName = $row->getData('lastname');
			if ($lastName != NULL) {
				return $firstName . ' ' . $lastName;
	                } else {
				return $firstName;
			}
	        } else {
        		return Mage::helper('imedia_rewardpoints')->__('NO NAME ASSIGNED');
	        }
    }
}