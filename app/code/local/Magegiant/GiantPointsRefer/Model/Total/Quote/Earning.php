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
class Magegiant_GiantPointsRefer_Model_Total_Quote_Earning
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_calculator;

    public function __construct()
    {
        $this->_calculator = Mage::getSingleton('giantpointsrefer/salesrule');
    }

    /**
     * Change collect total to Event to ensure earning is last runned total
     *
     * @param type $observer
     */

    public function salesQuoteCollectTotalsAfter($observer)
    {
        $quote = $observer['quote'];
        foreach ($quote->getAllAddresses() as $address) {
            if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
                continue;
            }
            $this->collect($address);
        }
    }

    /**
     * collect reward points that customer earned (per each item and address) total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote         $quote
     * @return Magegiant_GiantPoints_Model_Total_Quote_Point
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        // get points that customer can earned by Rates
        Mage::dispatchEvent('giantpoints_refer_collect_earning_total_points_before', array(
            'address' => $address,
        ));
        $_helper = Mage::helper('giantpointsrefer');
        /*=========Referral Program============*/
        $referralProgram = $_helper->getReferralProgram($quote);
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $address->setInviteeEarn($referralProgram->getInviteeEarn());
        }
        $address->setReferralId($referralProgram->getReferralId());
        $address->setReferralEarn($referralProgram->getReferralEarn());
        /*==========End Referral=========*/
        // Update earning point for each items
        $this->_updateItemEarningPoints($address);
        Mage::dispatchEvent('giantpointsrefer_collect_earning_total_points_after', array(
            'quote' => $quote,
        ));

        return $this;
    }

    /**
     * update earning points for address items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magegiant_GiantPoints_Model_Total_Quote_Earning
     */
    protected function _updateItemEarningPoints($address)
    {
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $eventArgs['item'] = $item;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_calculator->processEarnPoint($child, $address);
                    $eventArgs['item'] = $child;
                    Mage::dispatchEvent('giantpointsrefer_quote_address_item_earning_point', $eventArgs);
                }
            } else {
                $this->_calculator->processEarnPoint($item, $address);
                Mage::dispatchEvent('giantpointsrefer_quote_address_item_earning_point', $eventArgs);
            }

        }

        return $this;
    }

    /**
     * fetch
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this|array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => '1',
            'value' => 1,
        ));

        return $this;
    }
}
