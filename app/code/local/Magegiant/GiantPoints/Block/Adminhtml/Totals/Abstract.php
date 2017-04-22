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
 * Giantpoints Adminhtml Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Totals_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }

    public function getSpendingHelper()
    {
        return Mage::helper('giantpoints/calculation_spending');
    }

    public function getEarningHelper()
    {
        return Mage::helper('giantpoints/calculation_earning');
    }

    /**
     * get max point can deduct from customer balance
     *
     * @return int
     */
    public function getMaxInviteeEarnedRefund()
    {
        if (!$this->hasData('max_invitee_earned_refund')) {
            $maxEarnedRefund = 0;
            if ($creditmemo = $this->getCreditmemo()) {
                $order = $creditmemo->getOrder();

                $maxEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'invitee_invoiced')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getInviteeEarn()) {
                    $maxEarnedRefund = $order->getInviteeEarn();
                }
                $maxEarnedRefund += (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'invitee_refunded')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getInviteeEarn()) {
                    $maxEarnedRefund = $order->getInviteeEarn();
                }
                $this->_currentPoint->setInviteeRefundPoints($creditmemo->getInviteeEarn());
            }
            $this->setData('max_invitee_earned_refund', $maxEarnedRefund);
        }

        return $this->getData('max_invitee_earned_refund');
    }

    /**
     * @return boolean
     */
    public function canRefundSpentPoints()
    {
        $order            = $this->getCreditmemo()->getOrder();
        $allowRefundSpent = Mage::helper('giantpoints/config')->allowRefundSpentPoint($order->getStoreId());
        if ($order->getCustomerIsGuest() || !$allowRefundSpent) {
            return false;
        }
        if ($this->getMaxSpentPointRefund()) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function canRefundRateEarnedPoints()
    {
        $order             = $this->getCreditmemo()->getOrder();
        $allowRefundEarned = Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId());
        if ($order->getCustomerIsGuest() || !$allowRefundEarned) {
            return false;
        }
        if ($this->getMaxRateEarnedRefund()) {
            return true;
        }

        return false;
    }


    /**
     * get max point can deduct from customer balance
     *
     * @return int
     */
    public function getMaxRateEarnedRefund()
    {
        if (!$this->hasData('max_rate_earned_refund')) {
            $maxEarnedRefund = 0;
            if ($creditmemo = $this->getCreditmemo()) {
                $order = $creditmemo->getOrder();

                $maxEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'order_invoiced')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getGiantpointsEarn()) {
                    $maxEarnedRefund = $order->getGiantpointsEarn();
                }
                $maxEarnedRefund += (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'order_refunded')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getGiantpointsEarn()) {
                    $maxEarnedRefund = $order->getGiantpointsEarn();
                }
                $this->_currentPoint->setRateRefundPoints($creditmemo->getGiantpointsEarn());
            }
            $this->setData('max_rate_earned_refund', $maxEarnedRefund);
        }

        return $this->getData('max_rate_earned_refund');
    }

    /**
     * check admin can refund points that earned by invitee
     *
     * @return boolean
     */
    public function canRefundInviteeEarnedPoints()
    {
        $order             = $this->getCreditmemo()->getOrder();
        $allowRefundEarned = Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId());
        if ($order->getCustomerIsGuest() || !$allowRefundEarned) {
            return false;
        }
        if ($this->getMaxInviteeEarnedRefund()) {
            return true;
        }

        return false;
    }


    /**
     * check admin can refund points that earned by customer
     *
     * @return boolean
     */
    public function canRefundReferralEarnedPoints()
    {
        $order             = $this->getCreditmemo()->getOrder();
        $allowRefundEarned = Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId());
        if ($order->getCustomerIsGuest() || !$allowRefundEarned) {
            return false;
        }
        if ($this->getMaxReferralEarnedRefund()) {
            return true;
        }

        return false;
    }


    /**
     * get max point can deduct from customer balance
     *
     * @return int
     */
    public function getMaxReferralEarnedRefund()
    {
        if (!$this->hasData('max_referral_earned_refund')) {
            $maxEarnedRefund = 0;
            if ($creditmemo = $this->getCreditmemo()) {
                $order = $creditmemo->getOrder();

                $maxEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'referral_invoiced')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getReferralEarn()) {
                    $maxEarnedRefund = $order->getReferralEarn();
                }
                $maxEarnedRefund += (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'referral_refunded')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getReferralEarn()) {
                    $maxEarnedRefund = $order->getReferralEarn();
                }
                $this->_currentPoint->setReferralRefundPoints($creditmemo->getReferralEarn());
            }
            $this->setData('max_referral_earned_refund', $maxEarnedRefund);
        }

        return $this->getData('max_referral_earned_refund');
    }

    /**
     * max point that admin can refund to customer
     *
     * @return int
     */
    public function getMaxSpentPointRefund()
    {
        if ($this->hasData('max_spent_point_refund')) {
            return $this->getData('max_spent_point_refund');
        }
        $maxPointRefund = 0;
        if ($creditmemo = $this->getCreditmemo()) {
            $order = $creditmemo->getOrder();

            $maxPoint       = $order->getGiantpointsSpent();
            $maxPointRefund = $maxPoint - (int)Mage::getResourceModel('giantpoints/transaction_collection')
                    ->addFieldToFilter('action_code', 'spending_creditmemo')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
            if ($creditmemo->getGiantpointsDiscount()) {
                $currentPoint = ceil($maxPoint * $creditmemo->getGiantpointsDiscount() / $order->getGiantpointsDiscount());
            } else {
                $currentPoint = $maxPoint;
            }
            $this->setData('total_point', $maxPoint);
            $this->_currentPoint->setSpentRefundPoints(min($currentPoint, $maxPointRefund));
        }
        $this->setData('max_spent_point_refund', $maxPointRefund);

        return $this->getData('max_spent_point_refund');
    }
}