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
 * Giantpoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Total_Invoice_Earning extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total when create Invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_addInviteeEarned($invoice);
        $this->_addReferralEarned($invoice);

        return $this;
    }

    /**
     * process add point which earned for referral
     *
     * @param $invoice
     * @return $this
     */
    protected function _addReferralEarned($invoice)
    {
        $order     = $invoice->getOrder();
        $earnPoint = 0;
        $maxEarn   = $order->getReferralEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'order_invoiced_referral')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            $totalEarn = 0;
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getReferralEarn();
                if (!$item->isLast()) {
                    $itemPoint = ceil($itemPoint * $item->getQty() / $orderItem->getQtyOrdered());
                }
                $item->setReferralEarn($earnPoint);
                $totalEarn += $itemPoint;
            }
            if ($earnPoint >= $maxEarn)
                $totalEarn = $maxEarn;
            $invoice->setReferralEarn($totalEarn);
        }

        return $this;
    }

    /**
     * process add point which earned for referral link
     *
     * @param $invoice
     * @return $this
     */
    protected function _addInviteeEarned($invoice)
    {
        $order     = $invoice->getOrder();
        $earnPoint = 0;
        $maxEarn   = $order->getInviteeEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'invitee_invoiced')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            $totalEarn = 0;
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getInviteeEarn();
                if (!$item->isLast()) {
                    $itemPoint = ceil($itemPoint * $item->getQty() / $orderItem->getQtyOrdered());
                }
                $item->setInviteeEarn($earnPoint);
                $totalEarn += $itemPoint;
            }
            if ($earnPoint >= $maxEarn)
                $totalEarn = $maxEarn;
            $invoice->setInviteeEarn($totalEarn);
        }

        return $this;
    }
}
