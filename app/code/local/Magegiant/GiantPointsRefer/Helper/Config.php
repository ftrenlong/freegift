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
class Magegiant_GiantPointsRefer_Helper_Config extends Mage_Core_Helper_Abstract
{
    const REFERRAL_CONFIGURATION = 'giantpoints/referral_system_configuration/';

    public function getGeneralConfig($name, $store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_CONFIG . $name, $store);

    }

    /*==========Begin Referral configuration=============*/
    public function getReferralConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::REFERRAL_CONFIGURATION . $code, $storeId);
    }

    /**
     * check giant points is enabled
     *
     * @param null $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getReferralConfig('enable', $storeId);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getPointForReferralSignup($store = null)
    {
        return $this->getReferralConfig('points_for_referral_signup', $store);
    }

    /**
     * get referral discount type
     *
     * @param null $storeId
     * @return int
     */
    public function getReferralDiscountType($storeId = null)
    {
        return (int)$this->getReferralConfig('referral_discount_type', $storeId);
    }

    /**
     * get points commission for referral
     *
     * @param null $storeId
     * @return int
     */
    public function getPointsCommissionFix($storeId = null)
    {
        return (int)$this->getReferralConfig('points_commission_fix', $storeId);
    }

    /**
     * get point for referral by percent of total order
     *
     * @param null $storeId
     * @return int
     */
    public function getPointsCommissionPercent($storeId = null)
    {
        return (int)$this->getReferralConfig('points_commission_percent', $storeId);
    }

    public function getReferralConversionRate($storeId = null)
    {
        return $this->getReferralConfig('referral_conversion_rate', $storeId);
    }

    /**
     * point earned for referral will be limit by day
     *
     * @param null $storeId
     * @return int
     */
    public function getPointsCommissionDayLimit($storeId = null)
    {
        return (int)$this->getReferralConfig('points_commission_day_limit', $storeId);
    }

    /**
     * invited friend earn point for each order or first order config
     *
     * @param null $storeId
     * @return bool
     */
    public function getPointForOrderConfig($storeId = null)
    {
        return (int)$this->getReferralConfig('points_for_order', $storeId);
    }

    /**
     * get discount type for invited friend (fix point/ percent of order)
     *
     * @param null $storeId
     * @return bool
     */
    public function getInvitedDiscountType($storeId = null)
    {
        return (int)$this->getReferralConfig('invited_discount_type', $storeId);
    }

    /**
     * invited friend will fixed point
     *
     * @param null $storeId
     * @return int
     */
    public function getInvitedPointsFixed($storeId = null)
    {
        return (int)$this->getReferralConfig('points_for_order_fixed', $storeId);
    }

    /**
     * invited friend will earn point by percent of order
     *
     * @param null $storeId
     * @return int
     */
    public function getInvitedPointsPercent($storeId = null)
    {
        return (int)$this->getReferralConfig('points_for_order_percent', $storeId);
    }

    public function getInvitedConversionRate($storeId = null)
    {
        return $this->getReferralConfig('invited_conversion_rate', $storeId);
    }

    public function getIconUrl()
    {
        return Mage::getBaseUrl('media') . 'magegiant/giantpoints/icon/';
    }

    public function getSharingPageView($storeId = null)
    {
        return explode(',', $this->getReferralConfig('page_view', $storeId));
    }

    public function getAffiliateLinkRedirect($storeId = null)
    {
        $affiliateUrl = $this->getReferralConfig('affiliate_redirect_url', $storeId);
        if (Mage::helper('giantpoints/validation')->isValidUrl($affiliateUrl)) {
            $url = $affiliateUrl;
        } else {
            $url = Mage::getUrl($affiliateUrl);
        }

        return $url;
    }
    /*=================End Referral===============*/
}