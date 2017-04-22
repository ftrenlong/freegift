<?php

class Wyomind_Pickupatstore_Block_Adminhtml_Manage_Edit_Tabs extends Wyomind_Pickupatstore_Block_Adminhtml_Manage_Edit_AbstractTabs
{

    protected function _beforeToHtml()
    {

        parent::_beforeToHtml();
        if (null !== $this->getRequest()->getParam('place_id')) {
            $this->addTab(
                'orders', array(
                'label' => Mage::helper('pickupatstore')->__('Orders'),
                'title' => Mage::helper('pickupatstore')->__('Orders'),
                'content' => $this->getLayout()->createBlock('pickupatstore/adminhtml_manage_edit_tab_orders')->toHtml()
                    )
            );
        }

        return parent::_beforeToHtml();
    }

}
