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
class Magegiant_GiantPoints_Model_Calculation_Earning extends Magegiant_GiantPoints_Model_Calculation_Abstract
{

    /*Collect Quote*/
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote         = $address->getQuote();
        $_helperConfig = $this->getHelperConfig();
        if (!$_helperConfig->isEnabled($quote->getStoreId())) {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }

        // get points that customer can earned by Rates
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        Mage::dispatchEvent('giantpoints_collect_earning_total_points_before', array(
            'address' => $address,
        ));
        $_helperEarning = Mage::helper('giantpoints/calculation_earning');
		$_helperEarning->setQuote($quote);
        /*==========Earning by rate=========*/
        $totalEarnPoints = $_helperEarning->getTotalEarningPoints($quote);
        if ($totalEarnPoints > 0) {
            $address->setGiantpointsEarn($totalEarnPoints);
        } else {
            $address->setGiantpointsEarn(0);
        }
        /*=========End Earning by rate=========*/

        // Update earning point for each items
        $this->_processItemsEarning($address);
        Mage::dispatchEvent('giantpoints_collect_earning_total_points_after', array(
            'quote' => $quote,
        ));

        return $this;

    }

    protected function _processItemsEarning($address)
    {
        $items = $address->getAllItems();
        if (!count($items))
            return $this;
        $giantPointsEarn = $address->getGiantpointsEarn();
        $totalEarn       = 0;
        // Calculate total item prices
        $baseItemsPrice       = 0;
        $baseParentItemsPrice = array();
        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $baseParentItemsPrice[$item->getId()] = 0;
                foreach ($item->getChildren() as $child) {
                    $baseParentItemsPrice[$item->getId()] = $item->getQty() * ($child->getQty() * $this->_getItemBasePrice($child)) - $child->getBaseDiscountAmount();
                }
                $baseItemsPrice += $baseParentItemsPrice[$item->getId()];
            } else {
                $baseItemsPrice += $item->getQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount();
            }
        }

        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
            $eventArgs['item'] = $item;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemEarn = $giantPointsEarn * $baseParentItemsPrice[$item->getId()] / $baseItemsPrice;
                foreach ($item->getChildren() as $child) {
                    $baseItemPrice = $item->getQty() * ($child->getQty() * $this->_getItemBasePrice($child)) - $child->getBaseDiscountAmount();
                    $itemEarn      = $giantPointsEarn * $baseItemPrice / $baseItemsPrice;
                    $itemEarn      = round(min($itemEarn, $parentItemEarn), 0, PHP_ROUND_HALF_UP);
                    $maxItemEarn   = $giantPointsEarn - $totalEarn;
                    if ($itemEarn > $maxItemEarn)
                        $itemEarn = $maxItemEarn;
                    $totalEarn += $itemEarn;
                    $child->setGiantpointsEarn($itemEarn);
                    $eventArgs['item'] = $child;
                    Mage::dispatchEvent('giantpoints_quote_address_item_earning_point', $eventArgs);

                }
            } else {
                $baseItemPrice = $item->getQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount();
                $itemEarn      = round($giantPointsEarn * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_UP);
                $maxItemEarn   = $giantPointsEarn - $totalEarn;
                if ($itemEarn > $maxItemEarn)
                    $itemEarn = $maxItemEarn;
                $totalEarn += $itemEarn;
                $item->setGiantpointsEarn($itemEarn);
                Mage::dispatchEvent('giantpoints_quote_address_item_earning_point', $eventArgs);
            }
        }
    }

}