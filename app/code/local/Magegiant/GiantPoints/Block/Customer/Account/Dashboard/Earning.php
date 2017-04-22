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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Earning extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_earningRate;

    /**
     * get show earning on custom dashboard
     *
     * @return mixed
     */
    public function isShow()
    {
        $rate = $this->getPointEarningRate();
        if ($rate && $rate->getId()) {
            return true;
        }

        return false;
    }

    /**
     * convert money of rate to price
     *
     * @return string
     */
    public function getConvertRate($earnRate)
    {
        if ($earnRate && $earnRate->getId()) {
            $convertPrice = Mage::helper('giantpoints')->convertPrice($money = $earnRate->getMoney(), true);

            return $convertPrice;
        }

        return '';
    }

    /**
     * @return false|Magegiant_GiantPoints_Model_Rate
     */
    public function getPointEarningRate()
    {
        if (!$this->_earningRate) {
            $this->_earningRate = Mage::getModel('giantpoints/rate')->getConversionRate(Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT);
        }

        return $this->_earningRate;
    }


}
