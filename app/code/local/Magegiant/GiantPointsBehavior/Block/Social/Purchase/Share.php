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
class Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share extends Magegiant_GiantPoints_Block_Abstract
{
    public function getShowSharePurchase()
    {
        if (!Mage::helper('giantpointsbhv/config')->isEnabledPurchaseShare()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the proccessing action controller url.
     *
     * @return string
     */
    public function getProcessingUrl()
    {
        $isSecure = Mage::app()->getStore()->isCurrentlySecure();

        return $this->getUrl('giantpointsbhv/social_purchase_share/process', array('_secure' => $isSecure));
    }
}
