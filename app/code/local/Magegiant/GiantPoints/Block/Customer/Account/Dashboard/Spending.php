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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Spending extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_spendingRate;

    public function getCanShow()
    {
        $rate = $this->getSpendingRate();
        if ($rate && $rate->getId()) {
            return true;
        }

        return false;
    }

    /**
     * get spending rate
     *
     * @return Magegiant_GiantPoints_Model_Rate
     */
    public function getSpendingRate()
    {
        if (!$this->_spendingRate) {
            $this->_spendingRate = Mage::getModel('giantpoints/rate')->getConversionRate(Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY);
        }

        return $this->_spendingRate;
    }

    /**
     * get current money
     *
     * @param $rate
     * @return float|string
     */
    public function getCurrentMoney($rate)
    {
        if ($rate && $rate->getId()) {
            $convertPrice = Mage::app()->getStore()->convertPrice($rate->getMoney(), true);

            return $convertPrice;
        }

        return '';
    }
}
