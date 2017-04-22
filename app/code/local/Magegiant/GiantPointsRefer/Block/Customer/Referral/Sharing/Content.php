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
class Magegiant_GiantPointsRefer_Block_Customer_Referral_Sharing_Content extends Magegiant_GiantPointsRefer_Block_Customer_Referral_Sharing
{

    public function getFacebookIcon()
    {
        return $this->_helper->getIconUrl() . 'default/fb-icon.png';
    }

    public function getGoogleIcon()
    {
        return $this->_helper->getIconUrl() . 'default/gg-icon.png';
    }

    public function getTwitterIcon()
    {
        return $this->_helper->getIconUrl() . 'default/tweet-icon.png';
    }

    public function getTweetText()
    {
        return $this->__('WOW! Some amazing deals at %s! Check it out:', $this->getStoreTitle());
    }

    public function getShareUrl()
    {
        return $this->getCurrentUrl() . '?cod=' . $this->getReferralCode();
    }

}
