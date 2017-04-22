<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Rate extends Mage_Core_Model_Abstract
{

    protected $_currentCustomer;

    protected $_currentWebsite;
    /**
     * Spending Points
     */
    const POINT_TO_MONEY = 1;
    /**
     * Earning Points
     */
    const MONEY_TO_POINT = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/rate');
    }

    /**
     * exchange point to money
     *
     * @param $amount
     * @return float|int
     * @throws Exception
     */
    public function exchange($amount)
    {
        if (!$this->getPoints() || !$this->getMoney()) {
            throw new Exception(Mage::helper('giantpoints')->__('Exchange rates are incorrect'));
        }
        if ($this->getDirection() == self::POINT_TO_MONEY) {
            $newAmount = Mage::helper('giantpoints/config')->getRoundingMethod($amount * $this->getMoney() / $this->getPoints(), 2);
        } else {
            $newAmount = (int)Mage::helper('giantpoints/config')->getRoundingMethod(
                $amount * $this->getPoints() / $this->getMoney()
            );
        }

        return $newAmount;
    }

    /**
     * load rate by direction
     *
     * @param $direction
     * @return $this
     */
    public function loadByDirection($direction)
    {
        $this->getResource()->loadRateByCustomerWebsiteDirection(
            $this, $this->getCurrentCustomer(), $this->getCurrentWebsite(), $direction
        );

        return $this;
    }

    /**
     * @param int  $direction
     * @param null $customerGroupId
     * @param null $websiteId
     * @return null|Varien_Object
     */
    public function getConversionRate($direction = 1, $customerGroupId = null, $websiteId = null)
    {
        $websiteId = $websiteId ? $websiteId : Mage::app()->getStore()->getWebsiteId();
        if (!$customerGroupId) {
            $customerSession = Mage::getSingleton('customer/session');
            $customerGroupId = $customerSession->getCustomerGroupId();
        }
        $rates     = $this->getCollection()->filterConversionRate($direction, $websiteId, $customerGroupId);
        $applyRate = $rates->getFirstItem();
        if ($applyRate && $applyRate->getId()) {
            return $applyRate;
        }

        return null;
    }


    /**
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return Mage::app()->getWebsite($this->getWebsiteId());
    }

    /**
     * Get Curren Customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCurrentCustomer()
    {
        if (!$this->_currentCustomer) {
            $this->_currentCustomer = Mage::getModel('customer/session')->getCustomer();
        }

        return $this->_currentCustomer;
    }

    /**
     * Set current customer
     *
     * @param $customer
     * @return $this
     */
    public function setCurrentCustomer($customer)
    {
        $this->_currentCustomer = $customer;

        return $this;
    }

    public function getCurrentWebsite()
    {
        if (!$this->_currentWebsite) {
            $this->_currentWebsite = Mage::app()->getWebsite();
        }

        return $this->_currentWebsite;
    }

    /**
     * prepare customer group & website ids data before save to db
     *
     * @return Magegiant_GiantPoints_Model_Rate
     */
    protected function _beforeSave()
    {
        if (is_array($this->getWebsiteIds())) {
            $this->setWebsiteIds(implode(',', $this->getWebsiteIds()));
        }
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(implode(',', $this->getCustomerGroupIds()));
        }

        return parent::_beforeSave();
    }

}