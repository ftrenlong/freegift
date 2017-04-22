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
class Magegiant_GiantPointsRefer_Block_Customer_Referral_General extends Magegiant_GiantPoints_Block_Abstract
{

    protected $_helper;

    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('giantpointsrefer/config');
        $this->setTemplate('magegiant/giantpointsrefer/customer/referral/general.phtml');
    }

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
        return $this->_helper->isEnabled();
    }

    /**
     * get points for referral
     *
     * @return int
     */
    public function getPointsCommissionInfo()
    {
        $info         = '';
        $discountType = $this->_helper->getReferralDiscountType();
        if ($discountType == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            $pointsFixed = $this->_helper->getPointsCommissionFix();
            if ($pointsFixed)
                $info = $this->__('You will receive %s per purchasing made by your friends.', Mage::helper('giantpoints')->addLabelForPoint($pointsFixed));
        } else {
            $pointsPercent = $this->_helper->getPointsCommissionPercent();
            if ($pointsPercent)
                $info = $this->__('You will receive %s per purchasing made by your friends.', '<b>' . $pointsPercent . '%' . '</b>');
        }

        return $info;
    }

    /**
     * get points for invited customer who bought product via refer link
     *
     * @return int
     */
    public function getPointsInvitedInfo()
    {
        $info         = '';
        $discountType = $this->_helper->getInvitedDiscountType();
        if ($discountType == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            $pointsFixed = $this->_helper->getInvitedPointsFixed();
            if ($pointsFixed)
                $info = $this->__('Your friends will be received %s when purchasing at our store.', Mage::helper('giantpoints')->addLabelForPoint($pointsFixed));
        } else {
            $pointsPercent = $this->_helper->getInvitedPointsPercent();
            if ($pointsPercent)
                $info = $this->__('Your friends will be received %s when purchasing at our store.', '<b>' . $pointsPercent . '%' . '</b>');
        }

        return $info;

    }


    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl('customer/account/');
    }

    public function getReferralUrl()
    {
        return $this->getUrl('giantpointsrefer/invitation/promotion', array('cod' => $this->getReferralCode()));
    }

    public function getInvitationUrl()
    {
        return $this->getUrl('*/*/invitation');
    }
}
