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
class Magegiant_GiantPointsRefer_Model_Frontend_Observer extends Magegiant_GiantPoints_Model_Observer
{
    public function giantpointsBlockCheckoutEarningPointBefore($observer)
    {
        /*Point for Referral's*/
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }
        $container        = $event->getContainer();
        $pointAmount      = $container->getPointAmount();
        $earningInfo      = $container->getInfo();
        $customer         = Mage::helper('giantpoints/customer')->getCustomer();
        $pointForReferral = Mage::helper('giantpointsrefer')->getPointForInvitee($customer);
        if ($pointForReferral > 0.01) {
            $pointAmount += $pointForReferral;
            $earningInfo[] = new Varien_Object(array(
                'name'         => Mage::helper('giantpoints')->__('Invitee Earn'),
                'point_amount' => $pointForReferral,
            ));
            $container->setPointAmount($pointAmount);
            $container->setInfo($earningInfo);
        }

        return $this;
        /*end Referral*/
    }

}