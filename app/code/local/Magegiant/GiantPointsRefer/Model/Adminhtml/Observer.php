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
 * GiantPoints Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Adminhtml_Observer
{

    public function salesOrderCreditmemoRegisterBefore($observer)
    {
        $request    = $observer['request'];
        $creditmemo = $observer['creditmemo'];

        $input = $request->getParam('creditmemo');
        $order = $creditmemo->getOrder();
        if (Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId())) {

            //refund invitee earn
            $maxInviteeEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'invitee_invoiced')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($maxInviteeEarnedRefund > $order->getInviteeEarn()) {
                $maxInviteeEarnedRefund = $order->getInviteeEarn();
            }
            $maxInviteeEarnedRefund += (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'invitee_refunded')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($maxInviteeEarnedRefund > $order->getInviteeEarn()) {
                $maxInviteeEarnedRefund = $order->getInviteeEarn();
            }
            if (isset($input['refund_invitee_earned_points']) && $input['refund_invitee_earned_points'] > 0) {
                $refundPoints        = (int)$input['refund_invitee_earned_points'];
                $refundInviteePoints = min($refundPoints, $maxInviteeEarnedRefund);
                $creditmemo->setRefundInviteeEarnedPoints(max($refundInviteePoints, 0));
                $creditmemo->setInviteeEarn($creditmemo->getInviteeEarn());
            } else {
                $refundPoints        = (int)$creditmemo->getInviteeEarn();
                $refundInviteePoints = min($refundPoints, $maxInviteeEarnedRefund);
                $creditmemo->setRefundInviteeEarnedPoints(max($refundInviteePoints, 0));
                $creditmemo->setInviteeEarn($creditmemo->getInviteeEarn());
            }
            //refund referral points
            $maxReferralEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'referral_invoiced')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($maxReferralEarnedRefund > $order->getReferralEarn()) {
                $maxReferralEarnedRefund = $order->getReferralEarn();
            }
            $maxReferralEarnedRefund += (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'referral_refunded')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if (isset($input['refund_referral_earned_points']) && $input['refund_referral_earned_points'] > 0) {
                $refundPoints = (int)$input['refund_referral_earned_points'];
                if ($maxReferralEarnedRefund > $order->getReferralEarn()) {
                    $maxReferralEarnedRefund = $order->getReferralEarn();
                }
                $refundReferralPoints = min($refundPoints, $maxReferralEarnedRefund);
                $creditmemo->setRefundReferralEarnedPoints(max($refundReferralPoints, 0));
                $creditmemo->setReferralEarn($creditmemo->getReferralEarn());
            } else {
                $refundReferralPoints = min($creditmemo->getReferralEarn(), $maxReferralEarnedRefund);
                $creditmemo->setRefundReferralEarnedPoints(max($refundReferralPoints, 0));
                $creditmemo->setReferralEarn($creditmemo->getReferralEarn());
            }
        }
        return $this;
    }

    /**
     * Check module is enabled
     */
    protected function _isEnabledModule($storeId = null)
    {
        return (bool)Mage::helper('giantpointsrefer/config')->isEnabled($storeId);
    }
}