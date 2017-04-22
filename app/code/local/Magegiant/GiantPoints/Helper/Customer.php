<?php

/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Customer Account and Balance Helper
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Helper_Customer extends Mage_Core_Helper_Abstract
{
    protected $_customer;

    /**
     * @param $customer
     * @return bool|Magegiant_GiantPoints_Model_Customer
     */
    public function createRewardCustomer($customer)
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardAccount         = Mage::getModel('giantpoints/customer');
        $rewardAccount->setCustomerId($customer->getId());
        if ($isSubscribedByDefault) {
            $rewardAccount
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        try {
            $rewardAccount->save();
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);

            return false;
        }

        return $rewardAccount;
    }

    public function getCustomer()
    {
        $isAdmin = Mage::app()->getStore()->isAdmin();
        if ($isAdmin) {
            $this->_customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();

            return $this->_customer;
        }
        if (Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();

            return $this->_customer;
        }

        return $this->_customer;
    }

	public function getBalance(){
		$customer = $this->getCustomer();
		if($customer && $customer->getId()){
			$rewardCustomer = Mage::getModel('giantpoints/customer')->load($customer->getId(), 'customer_id');
			if($rewardCustomer && $rewardCustomer->getId()){
				return $rewardCustomer->getBalance();
			}
		}
		return 0;
	}
}
