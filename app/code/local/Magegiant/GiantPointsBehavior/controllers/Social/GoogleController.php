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
class Magegiant_GiantPointsBehavior_Social_GoogleController extends Mage_Core_Controller_Front_Action
{
    const CACHE_KEY_LAST_GOOGLE_AT = 'cache_last_google_at';

    /**
     * For +1 and un +1 products
     */
    public function processAction()
    {
        try {
            $action = $this->getRequest()->getParam('action', 'off');
            $link   = $this->getRequest()->getParam('link');
            // Check if customer is not logged in display a message and don't do any liking actions
            if (!Mage::helper('giantpoints/customer')->getCustomer()) {
                if ($action == 'on') {
                    throw new Exception($this->__("You must be logged in for us to reward you for +1'ing a page!"), 110);
                }
            }

            // Pull variables from the request
            $customer     = Mage::helper('giantpoints/customer')->getCustomer();
            $message      = array(
                'point_balance' => '',
                'description'   => ''
            );
            $pointsString = null;
            if ($action == 'on') {
                $pointEarned            = $this->_processOn($link, $customer);
                $message['description'] = Mage::helper('giantpoints')->__("Thanks for +1'ing this page!");
                if ($pointEarned) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointEarned);
                    $message['description'] = Mage::helper('giantpoints')->__("You've earned %s for +1'ing this page!", $pointsString);
                }
            } elseif ($action == 'off') {
                $pointRefund = $this->_processOff($link, $customer);
                if ($pointRefund) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointRefund);
                    $message['description'] = Mage::helper('giantpoints')->__("Your balance minus -%s for cancel +1'ing this page!", $pointsString);
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
                $message['description'] = Mage::helper('giantpoints')->__('There was a problem trying to reward you for +1\'ing this page.< br/>Try again and contact us if you still encounter this issue.');
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
     * Reward earned for G+1
     *
     * @param $link
     * @param $customer
     * @return int
     * @throws Exception
     */
    protected function _processOn($link, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (count($this->_alreadyUrl($rewardAccount->getId(), $link, 'google_on'))) {
            throw new Exception($this->__("You've already +1'd this page."), 120);
        }
        $_behaviorHelper = Mage::helper('giantpointsbhv/config');
        $googleRule      = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Google_PlusOne::ACTION_GOOGLE_PLUS);
        if ($googleRule && $googleRule->getId()) {
            $pointsForGoogle = $googleRule->getPointAmount();
        } else {
            $pointsForGoogle = $_behaviorHelper->getPointsForGoogle($customer->getStoreId());
        }
        if (!$pointsForGoogle)
            return 0;
        $pointsLimit = $_behaviorHelper->getGoogleLimitPerDay($customer->getStoreId());
        $pointAmount = $_behaviorHelper->limitAmountByDay($rewardAccount->getRewardId(), 'google_on', $pointsForGoogle, $pointsLimit);
        $waitingTime = $_behaviorHelper->getGoogleWaitingTime($customer->getStoreId());
        if ($this->_waiting($waitingTime)) {
            throw new Exception($this->__("Please wait %s second(s) before +1'ing another page if you want to be rewarded!", $waitingTime), 120);
        }
        if ($pointsForGoogle && !$pointAmount)
            throw new Exception($this->__("You've reached the Google+ rewards limit for today (%s point(s) per day)", $pointsLimit), 140);
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointAmount,
            'onhold_duration' => ($googleRule && $googleRule->getId()) ? $googleRule->getOnholdDuration() : 0
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => array('url' => $link),
        );
        Mage::helper('giantpoints/action')->createTransaction('google_on', $additionalData);

        return $pointAmount;
    }

    /**
     * Check has already +1 url
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
     * Wait for next +1
     *
     * @param $waitingTime
     * @return bool
     */
    protected function _waiting($waitingTime)
    {
        if (!$waitingTime)
            return false;
        if ($lastTime = Mage::app()->loadCache(self::CACHE_KEY_LAST_GOOGLE_AT)) {
            if (($lastTime + $waitingTime) > time())
                return true;
        }
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_GOOGLE_AT, array('waiting'), null);

        return false;
    }

    protected function _processOff($link, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (count($this->_alreadyUrl($rewardAccount->getId(), $link, 'google_off'))) {
            return 0;
        }
        if (count($alreadyLiked = $this->_alreadyUrl($rewardAccount->getId(), $link, 'google_on', Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED))) {
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
                'notice'        => array('url' => $link),
            );
            Mage::helper('giantpoints/action')->createTransaction('google_off', $additionalData);

            return $pointAmount;
        }

        return 0;
    }
}