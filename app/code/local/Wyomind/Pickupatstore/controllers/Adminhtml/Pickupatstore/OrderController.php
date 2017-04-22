<?php

class Wyomind_Pickupatstore_Adminhtml_Pickupatstore_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }
}
