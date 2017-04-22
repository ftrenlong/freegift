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
 * GiantPoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Total_Quote_Spending_Aftertax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_calculator;

    public function __construct()
    {
        $this->setCode('total_quote_spending_afterTax');
        $this->_calculator = Mage::getSingleton('giantpoints/calculation_spending');
    }

    /**
     * collect giantpoints points total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magegiant_GiantPoints_Model_Total_Quote_Point
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_isApplyTaxAfterDiscount($address)) {
            $this->_processHiddenTaxes($address);

            return $this;
        }
        $this->_calculator->collect($address);

        return $this;
    }

    protected function _isApplyTaxAfterDiscount($address)
    {
        $quote = $address->getQuote();
        $store = $quote->getStore();
        if (Mage::helper('tax')->applyTaxAfterDiscount($store)) {
            return true;
        }

        return false;
    }

    protected function _processHiddenTaxes($address)
    {
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_addHiddenTax($child, $address);
                }
            } else {
                $this->_addHiddenTax($item, $address);
            }
        }
        if ($address->getGiantpointsShippingHiddenTaxAmount()) {
            $address->addTotalAmount('shipping_hidden_tax', $address->getGiantpointsShippingHiddenTaxAmount());
            $address->addBaseTotalAmount('shipping_hidden_tax', $address->getGiantpointsBaseShippingHiddenTaxAmount());
        }
    }

    protected function _addHiddenTax($item, $address)
    {
        $item->setHiddenTaxAmount($item->getHiddenTaxAmount() + $item->getGiantpointsHiddenTaxAmount());
        $item->setBaseHiddenTaxAmount($item->getBaseHiddenTaxAmount() + $item->getGiantpointsBaseHiddenTaxAmount());

        $address->addTotalAmount('hidden_tax', $item->getGiantpointsHiddenTaxAmount());
        $address->addBaseTotalAmount('hidden_tax', $item->getGiantpointsBaseHiddenTaxAmount());
    }

    /**
     * add spending points row after tax into quote total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_isApplyTaxAfterDiscount($address)) {
            return $this;
        }
        $helperConfig = Mage::helper('giantpoints/config');
        if ($amount = $address->getGiantpointsDiscount()) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $helperConfig->getDiscountLabel(),
                'value' => -$amount,
            ));
        }

        return $this;
    }

}
