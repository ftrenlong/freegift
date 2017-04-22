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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Block_Customer_Referral_Register_Field extends Magegiant_GiantPoints_Block_Abstract
{
    protected function _toHtml()
    {
        if ($this->isEnabledReferral()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * check referral program is enabled or not
     *
     * @return boolean
     */
    public function isEnabledReferral()
    {
        return Mage::helper('giantpointsrefer/config')->isEnabled();
    }

    /**
     * get current affilate customer
     *
     * @return string
     */
    public function getCurrentAffiliate()
    {
        $cookie_key   = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $referal_code = Mage::getModel('giantpoints/cookie')->getCookie($cookie_key);
        if ($referal_code) {
            return $referal_code;
        }

        return '';

    }
}