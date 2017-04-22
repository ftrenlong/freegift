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
class Magegiant_GiantPointsRefer_Block_Customer_Referral_Sharing extends Magegiant_GiantPointsRefer_Block_Customer_Referral_General
{

    protected $_helper;

    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('giantpointsrefer/config');
    }

    public function getShareIconUrl()
    {
        return Mage::helper('giantpoints/config')->getShareIcon();
    }

    protected function _toHtml()
    {
        if ($this->_isShow()) {
            return parent::_toHtml();
        }

        return '';
    }

    protected function _isShow()
    {
        $page = $this->getCurrentPage();
        if (in_array($page, $this->_helper->getSharingPageView())) {
            return true;
        }

        return false;
    }

    public function getReferralContent()
    {
        return Mage::helper('core')->jsonEncode($this->getChildHtml('sharing_content'));
    }
}
