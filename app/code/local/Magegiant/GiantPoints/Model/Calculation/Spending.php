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
class Magegiant_GiantPoints_Model_Calculation_Spending extends Magegiant_GiantPoints_Model_Calculation_Abstract
{

    /*Collect Quote*/
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $helperConfig = Mage::helper('giantpoints/config');
        $quote        = $address->getQuote();
        if (!$helperConfig->isEnabled($quote->getStoreId())) {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getData('is_used_point')) {
            return $this;
        }
        Mage::dispatchEvent('giantpoints_collect_spending_total_points_before', array(
            'address' => $address,
        ));
        $rewardSalesRules   = $session->getRewardSalesRules();
        $rewardCheckedRules = $session->getRewardCheckedRules();
        $helper             = Mage::helper('giantpoints/calculation_spending');
		$helper->setQuote($quote);
        $maxPoints          = Mage::getModel('giantpoints/customer')->getBalance();
        if ($maxPointsPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
            $maxPoints = min($maxPointsPerOrder, $maxPoints);
        }
        $maxPoints -= $helper->getPointItemSpent();
        if ($maxPoints <= 0) {
            return $this;
        }
        $baseTotal = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseTotal += $this->getRowTotal($child);
                }
            } else {
                $baseTotal += $this->getRowTotal($item);
            }
        }
        if ($helperConfig->allowSpendPointForShipping()) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseTotal += $baseShippingAmount - $address->getBaseShippingDiscountAmount();
        }
        $baseDiscount = 0;
        $pointUsed    = 0;
        /*Process Checked rules*/
        if (is_array($rewardCheckedRules)) {
            $newRewardCheckedRules = array();
            foreach ($rewardCheckedRules as $ruleData) {
                if ($baseTotal < 0.0001)
                    break;
                $rule = $helper->getQuoteRule($ruleData['rule_id']);
                if (!$rule || !$rule->getId() || $rule->getSimpleAction() != 'fixed') {
                    continue;
                }
                if ($maxPoints < $rule->getPointAmount()) {
                    $session->addNotice($helper->__('Not enough points to spend'));
                    continue;
                }
                $points       = $rule->getPointAmount();
                $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                if ($ruleDiscount < 0.0001) {
                    continue;
                }

                $maxPoints -= $points;

                $baseDiscount += $ruleDiscount;
                $pointUsed += $points;

                $newRewardCheckedRules[$rule->getId()] = array(
                    'rule_id'       => $rule->getId(),
                    'point_amount'  => $points,
                    'base_discount' => $ruleDiscount,
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
            $session->setRewardCheckedRules($newRewardCheckedRules);
            Mage::dispatchEvent('totals_quote_reward_checked_rule', array(
                'rules' => $newRewardCheckedRules
            ));
        }
        /*Process Slider Rules*/
        if (is_array($rewardSalesRules)) {
            $newRewardSalesRules = array();
            if ($baseTotal > 0.001 && isset($rewardSalesRules['rule_id'])) {
                $rule = $helper->getQuoteRule($rewardSalesRules['rule_id']);
                if ($rule && $rule->getId() && $rule->getSimpleAction() == 'by_price') {
                    $points       = min($rewardSalesRules['point_amount'], $maxPoints);
                    $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                    if ($ruleDiscount > 0.0) {
                        $baseDiscount += $ruleDiscount;
                        $pointUsed += $points;
                        $newRewardSalesRules = array(
                            'rule_id'       => $rule->getId(),
                            'point_amount'  => $points,
                            'base_discount' => $ruleDiscount,
                        );
                    }
                }
            }
            $session->setRewardSalesRules($newRewardSalesRules);
            Mage::dispatchEvent('totals_quote_reward_sales_rule', array(
                'rules' => $newRewardSalesRules
            ));
        }
        $baseDiscount = min($baseTotal, $baseDiscount);
        if ($baseDiscount) {
            $discount = $quote->getStore()->convertPrice($baseDiscount);
            $address->setGiantpointsBaseDiscount($baseDiscount);
            $address->setGiantpointsDiscount($discount);

			$quote->setGiantpointsBaseDiscount($baseDiscount);
			$quote->setGiantpointsDiscount($discount);

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);
            $address->setGiantpointsSpent($pointUsed);
            $this->_processDiscountForItems($address);
        }

    }

    protected function _processDiscountForItems($address)
    {
        $items = $address->getAllItems();
        if (!count($items))
            return $this;

        $baseDiscount     = $address->getGiantpointsBaseDiscount();
        $giantPointsSpent = $address->getGiantpointsSpent();
        $totalSpent       = 0;
        // Calculate total item prices
        $baseItemsPrice       = 0;
        $helper               = $this->getHelperConfig();
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
        if ($baseItemsPrice < 0.0001)
            return $this;

        if ($baseItemsPrice < $baseDiscount && $helper->allowSpendPointForShipping()) {
            $baseDiscountForShipping = $baseDiscount - $baseItemsPrice;
            $baseDiscount            = $baseItemsPrice;
        } else {
            $baseDiscountForShipping = 0;
        }

        // Update discount for each item
        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
			$eventArgs['item'] = $item;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemBaseDiscount = $baseDiscount * $baseParentItemsPrice[$item->getId()] / $baseItemsPrice;
                $parentItemSpent        = $giantPointsSpent * $baseParentItemsPrice[$item->getId()] / $baseItemsPrice;
                foreach ($item->getChildren() as $child) {
                    if ($parentItemBaseDiscount <= 0)
                        break;
                    /*Discount*/
                    $baseItemPrice    = $item->getQty() * ($child->getQty() * $this->_getItemBasePrice($child)) - $child->getBaseDiscountAmount();
                    $itemBaseDiscount = min($baseItemPrice, $parentItemBaseDiscount);
                    $parentItemBaseDiscount -= $itemBaseDiscount;
                    /*Point Spent*/
                    $itemSpent    = $giantPointsSpent * $baseItemPrice / $baseItemsPrice;
                    $itemSpent    = round(min($itemSpent, $parentItemSpent), 0, PHP_ROUND_HALF_UP);
                    $maxItemSpent = $giantPointsSpent - $totalSpent;
                    if ($itemSpent > $maxItemSpent)
                        $itemSpent = $maxItemSpent;
                    $totalSpent += $itemSpent;
                    $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                    $child->setGiantpointsBaseDiscount($child->getGiantpointsBaseDiscount() + $itemBaseDiscount)
                        ->setGiantpointsDiscount($child->getGiantpointsDiscount() + $itemDiscount)
                        ->setGiantpointsSpent($itemSpent);

					Mage::dispatchEvent('giantpoints_quote_address_discount_item_after', $eventArgs);
                }
            } else {
                $baseItemPrice    = $item->getQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount();
                $itemBaseDiscount = $baseDiscount * $baseItemPrice / $baseItemsPrice;
                $itemDiscount     = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                $itemSpent        = round($giantPointsSpent * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_UP);
                $maxItemSpent     = $giantPointsSpent - $totalSpent;
                if ($itemSpent > $maxItemSpent)
                    $itemSpent = $maxItemSpent;
                $totalSpent += $itemSpent;
                $item->setGiantpointsBaseDiscount($item->getGiantpointsBaseDiscount() + $itemBaseDiscount)
                    ->setGiantpointsDiscount($item->getGiantpointsDiscount() + $itemDiscount)
                    ->setGiantpointsSpent($itemSpent);
				Mage::dispatchEvent('giantpoints_quote_address_discount_item_after', $eventArgs);
            }
        }
        if ($baseDiscountForShipping) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseShipping     = $baseShippingAmount - $address->getBaseShippingDiscountAmount();
            $itemBaseDiscount = ($baseDiscountForShipping <= $baseShipping) ? $baseDiscountForShipping : $baseShipping;
            $itemDiscount     = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
            $address->setGiantpointsBaseShippingDiscount($address->getGiantpointsBaseShippingDiscount() + $itemBaseDiscount)
                ->setGiantpointsShippingDiscount($address->getGiantpointsShippingDiscount() + $itemDiscount);
        }

		Mage::dispatchEvent('giantpoints_collect_spending_total_points_after', array(
			'address' => $address,
		));

        return $this;
    }

}