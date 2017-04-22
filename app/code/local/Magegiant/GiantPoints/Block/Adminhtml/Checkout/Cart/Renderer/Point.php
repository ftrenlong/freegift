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
class Magegiant_GiantPoints_Block_Adminhtml_Checkout_Cart_Renderer_Point extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    protected $_helperEarning;
    protected $_helperSpending;

    public function __construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/giantpoints/order/cart/renderer/point.phtml');
        $this->_helperEarning  = Mage::helper('giantpoints/calculation_earning');
        $this->_helperSpending = Mage::helper('giantpoints/calculation_spending');
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::helper('giantpoints/config')->isEnabled($this->getStore());
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    public function getSpendingHelper()
    {
        return $this->_helperSpending;
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    public function getEarningHelper()
    {
        return $this->_helperEarning;
    }

    /**
     * get total earning point
     *
     * @return int
     */
    public function getTotalEarnedPoint()
    {
        $totalEarn = $this->getEarningHelper()->getTotalEarningPoints();

        return $totalEarn;
    }

    /**
     * get total points that can use for order
     *
     * @return int
     */
    public function getTotalSpentPoint()
    {
        $totalSpend = $this->getSpendingHelper()->getTotalSpendingPoints();

        return $totalSpend;
    }

}
