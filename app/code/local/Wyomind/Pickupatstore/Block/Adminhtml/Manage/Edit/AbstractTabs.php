<?php

if (Mage::helper("core")->isModuleEnabled("Wyomind_Advancedinventory")) {

    class Wyomind_Pickupatstore_Block_Adminhtml_Manage_Edit_AbstractTabs extends Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Edit_Tabs
    {
        
    }

} else {

    class Wyomind_Pickupatstore_Block_Adminhtml_Manage_Edit_AbstractTabs extends Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tabs
    {
        
    }

}
