<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Customer_Birthday extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_CODE = 'giantpoints_customer_birthday';

    public function _construct()
    {
        $this->setCaption("Customer birthday occurs");
        $this->setDescription("Customer will get points on a birthday.");
        $this->setCode(self::ACTION_CODE);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_CODE => Mage::helper('giantpoints')->__('Customer birthday occurs'),
        );
    }

    public function visitAdminConditions(&$fieldset)
    {
        return $this;
    }

    public function visitAdminActions(&$fieldset)
    {
        return $this;
    }

    public function getNewActions()
    {
        return array();
    }

    public function getAdminFormScripts()
    {
        return array();
    }

    public function getAdminFormInitScripts()
    {
        return array();
    }

}