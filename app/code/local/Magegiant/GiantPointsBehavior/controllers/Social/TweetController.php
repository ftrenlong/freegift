<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Index Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsBehavior_Social_TweetController extends Mage_Core_Controller_Front_Action
{
    const CACHE_KEY_LAST_TWEET_AT = 'cache_last_tweet_at';

    /**
     * For liking and unliking products
     */
    public function processAction()
    {
        try {
            // Check if customer is not logged in display a message and don't do any liking actions
            if (!Mage::helper('giantpoints/customer')->getCustomer()) {
                throw new Exception($this->__("You must be logged in for us to reward you for tweeting."), 110);
            }
            $link = $this->getRequest()->getParam('link');
            // Pull variables from the request
            $customer               = Mage::helper('giantpoints/customer')->getCustomer();
            $message                = array(
                'point_balance' => '',
                'description'   => ''
            );
            $pointsString           = null;
            $pointEarned            = $this->_processTwitter($link, $customer);
            $message['description'] = Mage::helper('giantpoints')->__("Thanks for tweeting this page!");
            if ($pointEarned) {
                $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointEarned);
                $message['description'] = Mage::helper('giantpoints')->__("You've earned %s for tweeting!", $pointsString);
            }
            if ($pointsString) {
                $customer = Mage::helper('giantpoints/customer')->getCustomer();
                if ($customer && $customer->getId()) {
                    $point_balance            = Mage::helper('giantpoints')->addLabelForPoint(Mage::helper('giantpoints/customer')->getBalance());
                    $message['point_balance'] = '<a href="' . Mage::getUrl('giantpoints/index') . '">' . $point_balance . '</a>';
                }
            }
            $this->_responseMessage($message);
        } catch (Exception $ex) {
            // if error code > 100, it's user one and can be displayed
            if ($ex->getCode() > 100) {
                $message['description'] = $ex->getMessage();
            } else {
                $message['description'] = Mage::helper('giantpoints')->__('There was a problem trying to reward you for tweeting about this page.< br/>Try again and contact us if you still encounter this issue.');
            }

            $this->_responseMessage($message);
        }

        return $this;
    }

    /**
     * response ajax request
     *
     * @param $response
     * @return $this
     */

    protected function _responseMessage($response)
    {
        $this->getResponse()->setBody(json_encode($response));
        $this->getResponse()->setHeader('Content-Type', 'application/json');

        return $this;
    }

    /**
     * Reward earned for Twitter
     *
     * @param $link
     * @param $customer
     * @return int
     * @throws Exception
     */
    protected function _processTwitter($link, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (count($this->_alreadyUrl($rewardAccount->getId(), $link, 'tweet'))) {
            throw new Exception($this->__("You've already tweeted about this page."), 120);
        }
        $_behaviorHelper = Mage::helper('giantpointsbhv/config');
        $twitterRule     = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Twitter_Tweets::ACTION_TWITTER_TWEETS);
        if ($twitterRule && $twitterRule->getId()) {
            $pointsForTwitter = $twitterRule->getPointAmount();
        } else {
            $pointsForTwitter = $_behaviorHelper->getPointsForTwitter($customer->getStoreId());
        }
        if (!$pointsForTwitter)
            return 0;
        $pointsLimit = $_behaviorHelper->getTwitterLimitPerDay($customer->getStoreId());
        $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'tweet', $pointsForTwitter, $pointsLimit);
        $waitingTime = $_behaviorHelper->getTwitterWaitingTime($customer->getStoreId());
        if ($this->_waiting($waitingTime)) {
            throw new Exception($this->__("Please wait %s second(s) before tweeting another page if you want to be rewarded.", $waitingTime), 120);
        }
        if (!$pointAmount)
            throw new Exception($this->__("You've reached the tweet-rewards limit for today (%s point(s) per day)", $pointsLimit), 140);
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointAmount,
            'onhold_duration' => ($twitterRule && $twitterRule->getId()) ? $twitterRule->getOnholdDuration() : 0
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => array('url' => $link),
        );
        Mage::helper('giantpoints/action')->createTransaction('tweet', $additionalData);

        return $pointAmount;
    }

    /**
     * Check has already url
     *
     * @param $rewardId
     * @param $url
     * @param $actionCode
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _alreadyUrl($rewardId, $url, $actionCode)
    {
        return Mage::getResourceModel('giantpoints/transaction_collection')->hasAlreadyUrl($rewardId, $url, $actionCode);
    }

    /**
     * Wait for next Tweet
     *
     * @param $waitingTime
     * @return bool
     */
    protected function _waiting($waitingTime)
    {
        if (!$waitingTime)
            return false;
        if ($lastTime = Mage::app()->loadCache(self::CACHE_KEY_LAST_TWEET_AT)) {
            if (($lastTime + $waitingTime) > time())
                return true;
        }
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_TWEET_AT, array('waiting'), null);

        return false;
    }
}