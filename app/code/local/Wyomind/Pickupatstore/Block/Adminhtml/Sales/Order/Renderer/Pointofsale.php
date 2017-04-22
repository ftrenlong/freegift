<?php

class Wyomind_Pickupatstore_Block_Adminhtml_Sales_Order_Renderer_Pointofsale extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $pointofsaleName = "";
        $pointofsaleId = $row->getPickupPointofsaleId();
        
        if (null !== $pointofsaleId) {
            $pointofsale = Mage::getSingleton('pointofsale/pointofsale')->load($pointofsaleId);
            $pointofsaleName = $pointofsale->getName();
        }
        
        return $pointofsaleName;
    }
}
