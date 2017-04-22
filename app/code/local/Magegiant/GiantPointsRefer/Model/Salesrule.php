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
class Magegiant_GiantPointsRefer_Model_Salesrule extends Mage_Rule_Model_Rule
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpointsrefer/salesrule');
        $this->setIdFieldName('rule_id');
    }

    protected function _afterLoad()
    {
        $this->setConditions(null);
        $this->setActions(null);

        return parent::_afterLoad();
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('giantpointsrefer/salesrule_condition_combine');
    }


    public function getActionsInstance()
    {
        return Mage::getModel('giantpointsrefer/salesrule_condition_product_combine');
    }

    public function getResourceCollection()
    {
        return Mage::getResourceModel('giantpointsrefer/salesrule_collection');
    }

    public function checkRule($address)
    {
        if (!$this->getIsActive()) {
            return false;
        }

        $this->afterLoad();

        return $this->validate($address);
    }


    /*++++++++++++++++Calculator earning point+++++++++++++++++++*/
    public function processEarnPoint(Mage_Sales_Model_Quote_Item_Abstract $item, $address)
    {
		$address->setItemReferralEarned(0);
		$address->setItemInviteeEarned(0);

        $baseItemPrice = $this->_getItemBasePrice($item);
        $baseItemPrice -= $item->getBaseDiscountAmount();
        if ($baseItemPrice < 0) {
            return $this;
        }
        $qty          = $item->getTotalQty();
        $baseSubTotal = $address->getBaseSubtotalWithDiscount();
        /*Item earn point by invited*/
        $totalInviteeEarn = $address->getInviteeEarn();
        if ($totalInviteeEarn > 0.001 && $baseSubTotal > 0.001) {
            $ratio           = $totalInviteeEarn / $baseSubTotal;
            $itemInviteeEarn = round($qty * $baseItemPrice * $ratio);
            if ($itemInviteeEarn > 0.0001) {
                if ($item->getIsLast()) {
                    $itemInviteeEarn = $totalInviteeEarn - $address->getItemInviteeEarned();
                }
                $item->setInviteeEarn($itemInviteeEarn);
                $address->setItemInviteeEarned($address->getItemInviteeEarned() + $itemInviteeEarn);
            }
        }
        /*Item earn by referral*/
        $totalReferralEarn = $address->getReferralEarn();
        if ($totalReferralEarn > 0.001 && $baseSubTotal > 0.001) {
            $ratio            = $totalReferralEarn / $baseSubTotal;
            $itemReferralEarn = round($qty * $baseItemPrice * $ratio);
            if ($itemReferralEarn > 0.0001 && $baseSubTotal > 0.001) {
                $maxItemEarned = $totalReferralEarn - $address->getItemReferralEarned();
                if ($itemReferralEarn > $maxItemEarned) {
                    $itemReferralEarn = $maxItemEarned;
                }
                $item->setReferralEarn($itemReferralEarn);
                $address->setItemReferralEarned($address->getItemReferralEarned() + $itemReferralEarn);
            }
        }

        return $this;
    }

    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();

        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Return item price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemPrice($item)
    {
        $price     = $item->getDiscountCalculationPrice();
        $calcPrice = $item->getCalculationPrice();

        return ($price !== null) ? $price : $calcPrice;
    }

    /**
     * Return item original price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemOriginalPrice($item)
    {
        return Mage::helper('tax')->getPrice($item, $item->getOriginalPrice(), true);
    }

    /**
     * Return item base original price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemBaseOriginalPrice($item)
    {
        return Mage::helper('tax')->getPrice($item, $item->getBaseOriginalPrice(), true);
    }
}