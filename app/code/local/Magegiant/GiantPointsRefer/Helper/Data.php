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
 * @package     MageGiant_GiantPointsRefer
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsRefer Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsRefer
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Helper_Data extends Mage_Core_Helper_Abstract
{


    public function checkReferralOrderConfig($store = null)
    {
        if (!Mage::helper('giantpointsrefer/config')->isEnabled($store))
            return false;
        $referralConfig = Mage::helper('giantpointsrefer/config');
        $pointForOrder  = $referralConfig->getPointForOrderConfig($store);
        if ($pointForOrder == Magegiant_GiantPoints_Model_System_Config_Source_PointForOrder::FIRST_ORDER_ONLY) {
            $quote = $this->getQuote();
            $email = $quote->getCustomerEmail();
            zend_debug::dump($quote->getCustomerEmail());
            die();
        }
    }

    public function getReferralProgram($quote = null)
    {
        $referralProgram = new Varien_Object();
        $data            = array(
            'referral_id'   => 0,
            'invitee_earn'  => 0,
            'referral_earn' => 0
        );
        $referralProgram->setData($data);
        if (!$this->isEnabledReferralSystem())
            return $referralProgram;
        $code = $this->getReferralCode();
        if (!$code) {
            return $referralProgram;
        }
        $invitee_earn     = 0;
        $pointsCommission = 0;
        $customer_id      = Mage::helper('giantpoints/crypt')->decrypt($code);
        $customer         = Mage::getModel('customer/customer')->load($customer_id);
        if (!$customer || !$customer->getId())
            return $referralProgram;
        /*Check code used by themselves*/
        $current_customer = Mage::helper('giantpoints/customer')->getCustomer();
        if ($current_customer && $current_customer->getId() == $customer_id) {
            return $referralProgram;
        }
        /*calculator point for invitee*/
        $invitee_earn += $this->getPointForInvitee($current_customer);
        /*calculator point for referral*/
        $pointsCommission += $this->getPointCommission($customer);
        /*end referral*/
        $data = array(
            'referral_id'   => $customer_id,
            'invitee_earn'  => $invitee_earn,
            'referral_earn' => $pointsCommission
        );
        $referralProgram->setData($data);
        Mage::dispatchEvent('giantpoints_referrer_earning_before', array(
            'referral' => $referralProgram
        ));

        return $referralProgram;
    }

    /**
     * get All available salesrule
     *
     * @param null $quote
     * @param int  $type
     * @return array
     */
    public function getReferSalesRule($quote = null, $customer, $isReferral = true)
    {
        if (is_null($quote)) {
            $quote = Mage::getModel('checkout/session')->getQuote();
        }
        $appliedRules = array();
        if ($quote && count($quote->getAllItems())) {
            $ruleCollection = Mage::getModel('giantpointsrefer/salesrule')
                ->getCollection()
                ->addAvailableFilter()
                ->addFilterByWebsiteId(Mage::app()->getWebsite()->getId())
                ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_DESC);
            if ($isReferral) {
                if ($customer && $customer->getId()) {
                    $ruleCollection->addFilterByReferralsGroup($customer->getGroupId());
                }
            } else {
                $ruleCollection->addFilterByCustomerGroup($customer->getGroupId());
            }
            foreach ($ruleCollection as $rule) {
                if ($rule->checkRule($quote)) {
                    $appliedRules[] = $rule;
                    if ($rule->getStopRulesProcessing()) {
                        break;
                    }
                }
            }
        }

        return $appliedRules;
    }

    public function getQuote()
    {
        return Mage::getModel('checkout/session')->getQuote();
    }

    /**
     * @param $item
     */
    public function calcPointsForReferRule($rule, $quote, $type = '')
    {

        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $items    = $quote->getAllItems();
        $rowTotal = 0;
        $qtyTotal = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($rule->getActions()->validate($item)) {
                $rowTotal += max(0, $item->getBaseRowTotal() - $item->getBaseDiscountAmount());
                $qtyTotal += $item->getQty();
            }
        }
        if (!$qtyTotal) {
            return 0;
        }
        $helperConfig = Mage::helper('giantpoints/config');
        $earningPoint = 0;
        if ($helperConfig->getEarningByShipping($quote->getStoreId())) {
            $rowTotal += $address->getBaseShippingAmount();
        }
        if ($rule->getData($type . 'simple_action') == 'fixed') {
            $earningPoint = $rule->getData($type . 'point_amount');
        } else if ($rule->getData($type . 'simple_action') == 'by_price') {
            $earningPoint = Mage::helper('giantpoints/config')->getRoundingMethod($rule->getData($type . 'point_amount') * $rowTotal / $rule->getData($type . 'money_step'));
        }

        return $earningPoint;
    }

    /**
     * get point for invitee
     *
     * @return int
     */
    public function getPointForInvitee($customer)
    {
        if (!$this->isEnabledReferralSystem() || !$this->getReferralCode())
            return 0;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$this->checkFirstOrder($quote->getCustomerEmail(), $quote->getStoreId(), true)) {
            return 0;
        }
        $referralsRules = $this->getReferSalesRule($quote, $customer);
        $totalEarn      = 0;
        if (count($referralsRules)) {
            foreach ($referralsRules as $rule) {
                $totalEarn += Mage::helper('giantpointsrefer')->calcPointsForReferRule($rule, $quote, 'referrals_');
            }

            return $totalEarn;
        }
        $referralConfig = Mage::helper('giantpointsrefer/config');
        $amountToPoints = $this->getConversion()->getAmountToPoints();
        if ($referralConfig->getInvitedDiscountType() == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            return $referralConfig->getInvitedPointsFixed();
        } else {
            $invitedPointsPercent = $referralConfig->getInvitedPointsPercent();
            if ($invitedPointsPercent) {
                $amountEarn     = $amountToPoints * $invitedPointsPercent / 100;
                $conversionRate = explode(',', $referralConfig->getInvitedConversionRate());
                $newAmount      = (int)Mage::helper('giantpoints/config')->getRoundingMethod(
                    $amountEarn * $conversionRate['1'] / $conversionRate['0']
                );

                return $newAmount;
            }
        }
    }

    public function getConversion()
    {
        return Mage::helper('giantpoints/calculation_earning');
    }

    public function getPointCommission($customer)
    {
        if (!$this->isEnabledReferralSystem() || !$this->getReferralCode())
            return false;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$this->checkFirstOrder($quote->getCustomerEmail(), $quote->getStoreId())) {
            return 0;
        }
        $referralsRules = $this->getReferSalesRule($quote, $customer, false);
        $totalEarn      = 0;
        if (count($referralsRules)) {
            foreach ($referralsRules as $rule) {
                $totalEarn += Mage::helper('giantpointsrefer')->calcPointsForReferRule($rule, $quote);
            }

            return $totalEarn;
        }
        $referralConfig = Mage::helper('giantpointsrefer/config');
        $amountToPoints = $this->getConversion()->getAmountToPoints();
        if ($referralConfig->getReferralDiscountType() == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            return $referralConfig->getPointsCommissionFix();
        } else {
            $referralPointsPercent = $referralConfig->getPointsCommissionPercent();
            if ($referralPointsPercent) {
                $amountEarn     = $amountToPoints * $referralPointsPercent / 100;
                $conversionRate = explode(',', $referralConfig->getReferralConversionRate());
                $newAmount      = (int)Mage::helper('giantpoints/config')->getRoundingMethod(
                    $amountEarn * $conversionRate['1'] / $conversionRate['0']
                );

                return $newAmount;
            }
        }
    }

    public function checkFirstOrder($email, $store = null, $isReferral = false)
    {
        $referralConfig = Mage::helper('giantpointsrefer/config');
        $pointForOrder  = $referralConfig->getPointForOrderConfig($store);
        if ($pointForOrder == Magegiant_GiantPoints_Model_System_Config_Source_PointForOrder::FIRST_ORDER_ONLY) {
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('customer_email', $email);
            if ($isReferral) {
                $collection->addFieldToFilter('invitee_earn', array('gt' => 0));
            } else {
                $collection->addFieldToFilter('referral_earn', array('gt' => 0));
            }
            if ($collection->getSize())
                return false;
        }

        return true;

    }

    public function getReferralCode()
    {
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $code       = $cookie->getCookie($cookie_key);

        return $code;
    }

    public function isEnabledReferralSystem($store = null)
    {
        return Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsRefer') && Mage::helper('giantpointsrefer/config')->isEnabled($store);
    }

}