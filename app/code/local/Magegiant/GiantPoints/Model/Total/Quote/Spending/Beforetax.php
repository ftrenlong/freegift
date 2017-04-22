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
class Magegiant_GiantPoints_Model_Total_Quote_Spending_Beforetax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_calculator;

    public function __construct()
    {
        $this->setCode('giantpoints_spending_before_tax');
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
        if (!$this->_isApplyTaxAfterDiscount($address)) {
            return $this;
        }
        $this->_calculator->collect($address);
        $this->_processTaxForItems($address);

        return $this;
    }

    /**
     * @param $address
     */
    protected function _processTaxForItems(Mage_Sales_Model_Quote_Address $address)
    {
        $items = $address->getAllItems();
        if (!count($items))
            return $this;

        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_aggregateItemTax($child, $address);
                }
            } elseif ($item->getProduct()) {
                $this->_aggregateItemTax($item, $address);
            }
        }
        $store                   = Mage::app()->getStore();
        $baseDiscountForShipping = $address->getGiantpointsBaseShippingDiscount();
        if ($baseDiscountForShipping > 0) {
            $baseTaxableAmount = $address->getBaseShippingTaxable();
            $taxableAmount     = $address->getShippingTaxable();
            $address->setBaseShippingTaxable(max(0, $baseTaxableAmount - $baseDiscountForShipping));
            $address->setShippingTaxable(max(0, $taxableAmount - $address->getGiantpointsShippingDiscount()));

            if (Mage::helper('tax')->shippingPriceIncludesTax()) {
                $rate = $this->getShipingTaxRate($address, $store);
                if ($rate > 0) {
                    $address->setGiantpointsBaseShippingHiddenTaxAmount($address->getGiantpointsBaseShippingHiddenTaxAmount() + $this->calTax($baseTaxableAmount, $rate) - $this->calTax($address->getBaseShippingTaxable(), $rate));
                    $address->setGiantpointsShippingHiddenTaxAmount($address->getGiantpointsShippingHiddenTaxAmount() + $this->calTax($taxableAmount, $rate) - $this->calTax($address->getShippingTaxable(), $rate));
                }
            }
        }

        return $this;
    }

    protected function _aggregateItemTax($item, $address)
    {
        $store             = Mage::app()->getStore();
        $baseTaxableAmount = $item->getBaseTaxableAmount();
        $taxableAmount     = $item->getTaxableAmount();
        $taxQtyForUnit     = $this->_getTaxQtyForUnit($item, $store);
        $item->setBaseTaxableAmount(max(0, ($baseTaxableAmount - $item->getGiantpointsBaseDiscount() / $taxQtyForUnit)));
        $item->setTaxableAmount(max(0, ($taxableAmount - $item->getGiantpointsDiscount() / $taxQtyForUnit)));
        if (Mage::helper('tax')->priceIncludesTax()) {
            $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
            if ($rate > 0) {
                $item->setGiantpointsBaseHiddenTaxAmount(($item->getGiantpointsBaseHiddenTaxAmount() + $this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate)) * $taxQtyForUnit);
                $item->setGiantpointsHiddenTaxAmount(($item->getGiantpointsHiddenTaxAmount() + $this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate)) * $taxQtyForUnit);
            }
        }

        return $this;
    }

    protected function _getTaxQtyForUnit($item, $store)
    {
        if (Mage::helper('tax')->getConfig()->getAlgorithm($store) == Mage_Tax_Model_Calculation::CALC_UNIT_BASE) {
            return $item->getTotalQty();
        }

        return 1;
    }

    public function calTax($price, $rate)
    {
        return $this->round(Mage::getSingleton('tax/calculation')->calcTaxAmount($price, $rate, true, false));
    }

    public function getItemRateOnQuote($address, $product, $store)
    {
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
            );
            $rate    = Mage::getSingleton('tax/calculation')
                ->getRate($request->setProductClassId($taxClassId));

            return $rate;
        }

        return 0;
    }

    public function round($price)
    {
        return Mage::getSingleton('tax/calculation')->round($price);
    }

    public function getShipingTaxRate($address, $store)
    {
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
            $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
        );
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));
        $rate = Mage::getSingleton('tax/calculation')->getRate($request);

        return $rate;
    }


    public function _isApplyTaxAfterDiscount($address)
    {
        $quote = $address->getQuote();
        $store = $quote->getStore();
        if (Mage::helper('tax')->applyTaxAfterDiscount($store)) {
            return true;
        }

        return false;
    }

    /**
     * add spending points row after tax into quote total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (!$this->_isApplyTaxAfterDiscount($address)) {
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
