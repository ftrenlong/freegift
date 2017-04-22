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
class Magegiant_GiantPoints_Model_Calculation_Abstract extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function getRowTotal(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $baseItemPrice = $item->getTotalQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount();

        return $baseItemPrice;
    }

    public function getHelperConfig()
    {
        return Mage::helper('giantpoints/config');
    }

    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    public function getHelperTax()
    {
        return Mage::helper('tax');
    }


}