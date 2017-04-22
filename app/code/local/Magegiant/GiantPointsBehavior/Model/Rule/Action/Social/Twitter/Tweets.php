<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Twitter_Tweets extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{
    const ACTION_TWITTER_TWEETS = 'giantpoints_customer_twitter_tweets';

    public function _construct()
    {
        $this->setCaption("Twitter Tweet");
        $this->setDescription("Customer will get points when they tweet a page with Twitter.");
        $this->setCode(self::ACTION_TWITTER_TWEETS);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_TWITTER_TWEETS => Mage::helper('giantpoints')->__('Tweets a page with Twitter'),
        );
    }



}