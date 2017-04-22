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
 * GiantPoints Index Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_InvitationController extends Mage_Core_Controller_Front_Action
{

    public function promotionAction()
    {
        $code        = $this->getRequest()->getParam('cod', null);
        $customer_id = Mage::helper('giantpoints/crypt')->decrypt($code);
        $customer    = Mage::getModel('customer/customer')->load($customer_id);
        if (!$code || !$customer) {
            $this->_redirect('');
        }
        /*Check code used by themselves*/
        $current_customer = Mage::helper('giantpoints/customer')->getCustomer();
        if ($current_customer && $current_customer->getId() == $customer_id) {
            $this->_redirect('');
        }
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $cookie->setCookie($cookie_key, $code);
        $this->getResponse()->setRedirect(Mage::helper('giantpointsrefer/config')->getAffiliateLinkRedirect(), 301);
    }
}