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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Calculation_Earning extends Magegiant_GiantPoints_Helper_Calculation_Abstract
{

    /**
     * @param null $quote
     * @return int|mixed
     */
    public function getTotalEarningPoints($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        $customer      = Mage::getModel('giantpoints/customer')->getCustomer();
        $amountToPoint = $this->getAmountToPoints($quote);
        /*Earning rate*/
        $totalEarnPoints = $this->getEarningByRate($amountToPoint, $quote->getStoreId());
        $container       = new Varien_Object(array(
            'total_earn_points' => $totalEarnPoints,
        ));
        Mage::dispatchEvent('giantpoints_calculation_earning_total_points', array(
            'quote'     => $quote,
            'container' => $container,
        ));
        /*Check max earning points*/
        $totalEarnPoints          = $container->getTotalEarnPoints();
        $maximumPointsPerCustomer = Mage::helper('giantpoints/config')->getMaxPointPerCustomer();
        if ($maximumPointsPerCustomer) {
            $customersPoints = 0;
            if ($customer) {
                $customersPoints = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer)->getBalance();
            }
            if ($totalEarnPoints + $customersPoints > $maximumPointsPerCustomer) {
                $totalEarnPoints = $maximumPointsPerCustomer - $customersPoints;
            }
        }
        $totalEarnPoints = max($totalEarnPoints, 0);

        return $totalEarnPoints;
    }

    public function getAmountToPoints($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        $address                  = $this->getAddress($quote);
        $helperConfig             = Mage::helper('giantpoints/config');
        $pointsEarningCalculation = $helperConfig->getPointsEarningCalculation();
        $baseSubtotalWithDiscount = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
        $baseSubtotalWithDiscount -= $address->getGiantpointsBaseDiscount();
        if (Mage::helper('giantpoints/config')->getEarningByShipping($quote->getStoreId())) {
            $baseSubtotalWithDiscount += $address->getBaseShippingAmount();
        }

        if ($pointsEarningCalculation != Magegiant_GiantPoints_Model_System_Config_Source_Calculation::POINTS_BEFORE_TAX) {
            $baseSubtotalWithDiscount += $address->getBaseTaxAmount();
        }
        $baseGrandTotal = max(0, $baseSubtotalWithDiscount);

        return $baseGrandTotal;
    }


    /**
     * @param float $baseGrandTotal
     * @param mixed $store
     * @return int
     */
    public function getEarningByRate($baseGrandTotal, $store = null)
    {
        $customer = $this->getQuote()->getCustomer();
//        if ($customer && $customer->getId()) {
//            $customerGroupId = $customer->getGroupId();
//            $websiteId       = $customer->getWebsiteId();
//        } else {

            $customerGroupId = $this->getCustomerGroupId();
            $websiteId       = $this->getWebsiteId();
//        }
        $cacheKey = "earning_rate_points:$customerGroupId:$websiteId:$baseGrandTotal";
        if (Mage::helper('giantpoints/cache')->hasCache($cacheKey)) {
            return Mage::helper('giantpoints/cache')->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('giantpoints/rate')->getConversionRate(
            Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            if ($baseGrandTotal < 0) {
                $baseGrandTotal = 0;
            }
            $points = Mage::helper('giantpoints/config')->getRoundingMethod(
                $baseGrandTotal * $rate->getPoints() / $rate->getMoney(), $store
            );
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, $points);
        } else {
            Mage::helper('giantpoints/cache')->saveCache($cacheKey, 0);
        }

        return Mage::helper('giantpoints/cache')->getCache($cacheKey);
    }

    public function getEarnedPointsSummary($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        $address = $this->getAddress($quote);

        return $address->getGiantpointsEarn();
    }


}