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
class Magegiant_GiantPointsBehavior_Social_FacebookController extends Mage_Core_Controller_Front_Action
{
    const CACHE_KEY_LAST_FACEBOOK_LIKE_AT = 'cache_last_facebook_like_at';

    /**
     * For liking and unliking products
     */
    public function processAction()
    {
        try {
            $action    = $this->getRequest()->getParam('action', 'unlike');
            $liked_url = $this->getRequest()->getParam('liked_url');
            // Check if customer is not logged in display a message and don't do any liking actions
            if (!Mage::helper('giantpoints/customer')->getCustomer()) {
                if ($action == 'like') {
                    throw new Exception($this->__("You must be logged in for us to reward you for Facebook-Liking a page!"), 110);
                }
            }

            // Pull variables from the request
            $customer     = Mage::helper('giantpoints/customer')->getCustomer();
            $message      = array(
                'point_balance' => '',
                'description'   => ''
            );
            $pointsString = null;
            if ($action == 'like') {
                $pointEarned            = $this->_processLike($liked_url, $customer);
                $message['description'] = Mage::helper('giantpoints')->__("Thanks for Liking this page!");
                if ($pointEarned) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointEarned);
                    $message['description'] = Mage::helper('giantpoints')->__("You've earned %s for Liking this page!", $pointsString);

                }
            } elseif ($action == 'unlike') {
                $pointRefund = $this->_processUnlike($liked_url, $customer);
                if ($pointRefund) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointRefund);
                    $message['description'] = Mage::helper('giantpoints')->__("Your balance minus -%s for Un-liking this page!", $pointsString);
                }
            } else {
                $message['description'] = $this->__('Invalid action');
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
                $message['description'] = Mage::helper('giantpoints')->__('There was a problem trying to reward you for liking this page on Facebook.<br/>Try again and contact us if you still encounter this issue.');
            }
            $this->_responseMessage($message);
        }

        return $this;
    }

    /**
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
     * Reward earned for Facebook Like
     *
     * @param $liked_url
     * @param $customer
     * @return int
     * @throws Exception
     */
    protected function _processLike($liked_url, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return 0;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (count($this->_alreadyUrl($rewardAccount->getId(), $liked_url, 'facebook_like'))) {
            throw new Exception($this->__("You've already liked this page."), 120);
        }
        $_behaviorHelper = Mage::helper('giantpointsbhv/config');
        $facebookRule    = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Facebook_Like::ACTION_LIKE_FACEBOOK);
        if ($facebookRule && $facebookRule->getId()) {
            $pointsForFacebook = $facebookRule->getPointAmount();
        } else {
            $pointsForFacebook = $_behaviorHelper->getPointsForFacebook($customer->getStoreId());
        }
        if (!$pointsForFacebook)
            return 0;
        $pointsLimit = $_behaviorHelper->getFacebookLimitPerDay($customer->getStoreId());
        $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'facebook_like', $pointsForFacebook, $pointsLimit);
        $waitingTime = $_behaviorHelper->getFacebookWaitingTime($customer->getStoreId());
        if ($this->_waiting($waitingTime)) {
            throw new Exception($this->__("You have to wait at least %s seconds for the next like!.", $waitingTime), 120);
        }
        if ($pointsForFacebook && !$pointAmount)
            throw new Exception($this->__("You've reached the Like rewards limit for today (%s point(s) per day)", $pointsLimit), 140);
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointAmount,
            'onhold_duration' => ($facebookRule && $facebookRule->getId()) ? $facebookRule->getOnholdDuration() : 0

        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => array('url' => $liked_url),
        );
        Mage::helper('giantpoints/action')->createTransaction('facebook_like', $additionalData);

        return $pointAmount;
    }

    /**
     * Check has already Url
     *
     * @param $rewardId
     * @param $url
     * @param $actionCode
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _alreadyUrl($rewardId, $url, $actionCode, $status = null)
    {
        return Mage::getResourceModel('giantpoints/transaction_collection')->hasAlreadyUrl($rewardId, $url, $actionCode, $status);
    }

    /**
     * Wait for next Like
     *
     * @param $waitingTime
     * @return bool
     */
    protected function _waiting($waitingTime)
    {
        if (!$waitingTime)
            return false;
        if ($lastTime = Mage::app()->loadCache(self::CACHE_KEY_LAST_FACEBOOK_LIKE_AT)) {
            if (($lastTime + $waitingTime) > time())
                return true;
        }
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_FACEBOOK_LIKE_AT, array('waiting'), null);

        return false;
    }

    protected function _processUnlike($liked_url, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (count($this->_alreadyUrl($rewardAccount->getId(), $liked_url, 'facebook_unlike'))) {
            return 0;
        }
        $alreadyLiked = $this->_alreadyUrl($rewardAccount->getId(), $liked_url, 'facebook_like', Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED);
        if (count($alreadyLiked)) {
            $liked = $alreadyLiked->getFirstItem();

            $pointAmount = (int)$liked->getPointBalance();
            if (!$pointAmount)
                return 0;
            $obj            = new Varien_Object(array(
                'point_amount' => -$pointAmount,
            ));
            $additionalData = array(
                'customer'      => $customer,
                'action_object' => $obj,
                'notice'        => array('url' => $liked_url),
            );
            Mage::helper('giantpoints/action')->createTransaction('facebook_unlike', $additionalData);

            return $pointAmount;
        }

        return 0;
    }
}