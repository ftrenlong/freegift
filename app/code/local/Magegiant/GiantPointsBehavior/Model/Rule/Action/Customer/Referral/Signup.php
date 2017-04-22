<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Customer_Referral_Signup extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_REFERRAL_SIGNUP = 'giantpoints_customer_referral_signup';

    public function _construct()
    {
        $this->setCaption("Customer Referral");
        $this->setDescription("Customer will get points for every purchase made by a referred customer.");
        $this->setCode(self::ACTION_REFERRAL_SIGNUP);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_REFERRAL_SIGNUP => Mage::helper('giantpoints')->__('Referral signs up to website'),
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