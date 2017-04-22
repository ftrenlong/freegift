<?php

class Magegiant_GiantPointsRefer_Model_Rule_Action_Referrals_Signup extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_CODE = 'giantpoints_referrals_signup';

    public function _construct()
    {
        $this->setCaption("Google +1");
        $this->setDescription("Customer will get points when your friends signup to website");
        $this->setCode(self::ACTION_CODE);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_CODE => Mage::helper('giantpoints')->__('Referrals Signup'),
        );
    }

}