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
class Magegiant_GiantPoints_Block_Totals_Invoice_Point extends Magegiant_GiantPoints_Block_Abstract
{
    public function initTotals()
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $totalsBlock  = $this->getParentBlock();
        $invoice      = $totalsBlock->getInvoice();
        $helper       = Mage::helper('giantpoints');
        $helperConfig = Mage::helper('giantpoints/config');
        $storeId      = $invoice->getStoreId();
        if ($invoice->getGiantpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Earned'),
                'value'       => $helper->addLabelForPoint($invoice->getGiantpointsEarn(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($invoice->getGiantpointsSpent()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_spend_label',
                'label'       => $this->__('Spent'),
                'value'       => $helper->addLabelForPoint($invoice->getGiantpointsSpent(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($invoice->getInviteeEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'invitation_earn_label',
                'label'       => $this->__('Invitation Earned'),
                'value'       => $helper->addLabelForPoint($invoice->getInviteeEarn(), $storeId),
                'is_formated' => true,
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
}
