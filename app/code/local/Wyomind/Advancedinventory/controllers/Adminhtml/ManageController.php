<?php

require_once Mage::getModuleDir('controllers', 'Wyomind_Pointofsale') . DS . 'Adminhtml' . DS . 'ManageController.php';

class Wyomind_Advancedinventory_Adminhtml_ManageController extends Wyomind_Pointofsale_Adminhtml_ManageController
{
    public function editAction()
    {
        $id = $this->getRequest()->getParam('place_id');
        
        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $allowedPointofsales = $permissions->getPos();
        $isAdmin = $permissions->isAdmin();
        
        if (false === $isAdmin) {
            $allowedPointofsales = $permissions->getPos();
        
            if (false === in_array($id, $allowedPointofsales)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancedinventory')->__('Point of sale not reachable'));
                return $this->_redirect("*/*/");
            }
        }
        
        parent::editAction();
    }
}
