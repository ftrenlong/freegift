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
 * Giantpoints Total Point Spend Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Totals_Invoice_Point extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $invoice     = $totalsBlock->getInvoice();
        if (!$invoice)
            return $this;
        $helper       = Mage::helper('giantpoints');
        $helperConfig = Mage::helper('giantpoints/config');
        $storeId      = $invoice->getStoreId();
        if ($invoice->getGiantpointsEarn() > 0.001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Earned'),
                'value'       => $helper->addLabelForPoint($invoice->getGiantpointsEarn(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'last');
        }
        if ($invoice->getGiantpointsSpent() > 0.001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_spend_label',
                'label'       => $this->__('Spent'),
                'value'       => $helper->addLabelForPoint($invoice->getGiantpointsSpent(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'last');
        }
        if ($invoice->getInviteeEarn() > 0.001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'invitation_earn_label',
                'label'       => $this->__('Invitee Earned'),
                'value'       => $helper->addLabelForPoint($invoice->getInviteeEarn(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($invoice->getReferralEarn() > 0.001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'referral_earn_label',
                'label'       => $this->__('Referrer\'s Earned'),
                'value'       => $helper->addLabelForPoint($invoice->getReferralEarn(), $storeId),
                'is_formated' => true,
                'strong'      => true,
            )), 'subtotal');
        }
        if ($invoice->getGiantpointsDiscount() > 0.001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'giantpoints_discount',
                'label' => $helperConfig->getDiscountLabel(),
                'value' => -$invoice->getGiantpointsDiscount(),
            )), 'subtotal');
        }
    }

    /**
     *
     * @param $points
     * @param $store
     * @return string
     */
    protected function _addPointLabel($points, $store)
    {
        return Mage::helper('giantpoints')->addLabelForPoint($points, $store);
    }
}
