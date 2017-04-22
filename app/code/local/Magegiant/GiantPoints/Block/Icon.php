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
class Magegiant_GiantPoints_Block_Icon extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_giantPointsHtml = null;

    /**
     * @return Mage_Core_Block_Abstract
     */
    public function _prepareLayout()
    {
        $this->setTemplate('magegiant/giantpoints/customer/account/icon.phtml');

        return parent::_prepareLayout();
    }

    /**
     * Render points icon html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('giantpoints/config')->isShowPointIcon()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * get Reward Info Url
     *
     * @return string
     */
    public function getRewardInfoUrl()
    {
        $url = Mage::helper('giantpoints/config')->getRewardInfoUrl();
        if ($url)
            return $url;

        return '';
    }
}
