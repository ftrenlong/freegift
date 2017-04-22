<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Google_PlusOne extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_GOOGLE_PLUS = 'giantpoints_customer_google_plus';

    public function _construct()
    {
        $this->setCaption("Google +1");
        $this->setDescription("Customer will get points when they +1 a page with Google+.");
        $this->setCode(self::ACTION_GOOGLE_PLUS);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_GOOGLE_PLUS => Mage::helper('giantpoints')->__('+1\'s a page with Google+'),
        );
    }

}