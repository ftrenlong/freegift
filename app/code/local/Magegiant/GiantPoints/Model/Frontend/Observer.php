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
 * GiantPoints Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Frontend_Observer
{

    /**
     *
     * @param type $observer
     */
    public function paypalPrepareLineItems($observer)
    {
        $discountLabel = Mage::helper('giantpoints/config')->getDiscountLabel();
        if ($paypalCart = $observer->getPaypalCart()) {
            $salesEntity = $paypalCart->getSalesEntity();

            $baseDiscount = $salesEntity->getGiantpointsBaseDiscount();
            if ($baseDiscount > 0.0001) {
                $paypalCart->updateTotal(
                    Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, (float)$baseDiscount, $discountLabel
                );
            }
        }

        return $this;
    }
}