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
class Magegiant_GiantPointsBehavior_Social_Purchase_ShareController extends Mage_Core_Controller_Front_Action
{

    /**
     * Puschase share earn point proccess
     *
     * @return $this
     */
    public function processAction()
    {
        try {
            $action     = $this->getRequest()->getParam('action', '');
            $product_id = $this->getRequest()->getParam('product_id');
            $order_id   = $this->getRequest()->getParam('order_id');
            if (!$product_id) {
                throw new Exception($this->__("No product found to reward sharing your purchase."), 10);
            }
            if (!$action) {
                throw new Exception($this->__("No action type found to reward sharing your purchase."), 20);
            }
            if (!$order_id) {
                throw new Exception($this->__("No order found to reward sharing your purchase."), 30);
            }
            // Check if customer is not logged in display a message and don't do any liking actions
            $customer = Mage::helper('giantpoints/customer')->getCustomer();
            if (!$customer || !$customer->getId()) {
                if ($action == 'facebook') {
                    throw new Exception($this->__("You must be logged in for us to reward you for shares a purchase on Facebook.!"), 110);
                } else
                    throw new Exception($this->__("You must be logged in for us to reward you for Twitter!"), 110);
            }

            // Pull variables from the request
            $message      = array(
                'point_balance' => '',
                'description'   => ''
            );
            $pointsString = '';
            if ($action == 'facebook') {
                $pointEarned            = $this->_processFacebook($customer, $product_id, $order_id);
                $message['description'] = Mage::helper('giantpoints')->__("Thanks for sharing this purchase on Facebook!");
                if ($pointEarned) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointEarned);
                    $message['description'] = Mage::helper('giantpoints')->__("You've earned %s for sharing this purchase on Facebook!", $pointsString);

                }
            } else if ($action == 'twitter') {
                $pointEarned            = $this->_processTwitter($customer, $product_id, $order_id);
                $message['description'] = Mage::helper('giantpoints')->__("Thanks for sharing this purchase on Twitter!");
                if ($pointEarned) {
                    $pointsString           = Mage::helper('giantpoints')->addLabelForPoint($pointEarned);
                    $message['description'] = Mage::helper('giantpoints')->__("You've earned %s for sharing this purchase on Twitter!", $pointsString);
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
        } catch (Exception $ex) {
            // if error code > 100, it's user one and can be displayed
            if ($ex->getCode() > 100) {
                $message['description'] = $ex->getMessage();
            } else {
                $message['description'] = Mage::helper('giantpoints')->__('There was a problem trying to reward you for liking this page on Facebook.<br/>Try again and contact us if you still encounter this issue.');
            }
            $message['description'] = $ex->getMessage();
        }
        $this->_responseMessage($message);

        return $this;
    }

    protected function _responseMessage($response)
    {
        $this->getResponse()->setBody(json_encode($response));
        $this->getResponse()->setHeader('Content-Type', 'application/json');

        return $this;
    }

    /**
     * Reward earned for share a puschase product
     *
     * @param $customer
     * @param $product_id
     * @param $order_id
     * @return int
     * @throws Exception
     */
    protected function _processFacebook($customer, $product_id, $order_id)
    {
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $rewardAccount = Mage::helper('giantpoints/customer')->createRewardCustomer($customer);
        }
        if (count($this->_alreadyPurchase($rewardAccount->getId(), 'purchase_facebook_share', $product_id, $order_id))) {
            throw new Exception($this->__("You've already been rewarded for sharing this purchase on Facebook."), 120);
        }
        $facebookRule = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Purchase_Facebook_Share::ACTION_CODE);
        if (!$facebookRule || !$facebookRule->getId()) {
            return 0;
        }
        $pointsForFacebook = $facebookRule->getPointAmount();
        if (!$pointsForFacebook)
            return 0;
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointsForFacebook,
            'onhold_duration' => $facebookRule->getOnholdDuration()
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => array(
                'product_id' => $product_id,
                'order_id'   => $order_id
            ),
        );
        Mage::helper('giantpoints/action')->createTransaction('purchase_facebook_share', $additionalData);

        return $pointsForFacebook;
    }

    protected function _alreadyPurchase($rewardId, $actionCode, $product_id, $order_id)
    {
        return Mage::getResourceModel('giantpoints/transaction_collection')->hasAlreadyPurchase($rewardId, $actionCode, $product_id, $order_id);
    }

    protected function _processTwitter($customer, $product_id, $order_id)
    {
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $rewardAccount = Mage::helper('giantpoints/customer')->createRewardCustomer($customer);
        }
        if (count($this->_alreadyPurchase($rewardAccount->getId(), 'purchase_twitter_tweet', $product_id, $order_id))) {
            throw new Exception($this->__("You've already been rewarded for sharing this purchase on Twitter."), 120);
        }
        $twitter_rule = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPointsBehavior_Model_Rule_Action_Social_Purchase_Twitter_Tweet::ACTION_CODE);
        if (!$twitter_rule || !$twitter_rule->getId()) {
            return 0;
        }
        $pointsForTwitter = $twitter_rule->getPointAmount();
        if (!$pointsForTwitter)
            return 0;
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointsForTwitter,
            'onhold_duration' => $twitter_rule->getOnholdDuration()
        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => array(
                'product_id' => $product_id,
                'order_id'   => $order_id
            ),
        );
        Mage::helper('giantpoints/action')->createTransaction('purchase_twitter_tweet', $additionalData);

        return $pointsForTwitter;

    }

}