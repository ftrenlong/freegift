<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Facebook_Like extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_LIKE_FACEBOOK = 'giantpoints_customer_facebook_like';

    public function _construct()
    {
        $this->setCaption("Likes a page with Facebook");
        $this->setDescription("Customer will get points when they like a page with Facebook.");
        $this->setCode(self::ACTION_LIKE_FACEBOOK);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_LIKE_FACEBOOK => Mage::helper('giantpoints')->__('Likes a page with Facebook'),
        );
    }


}