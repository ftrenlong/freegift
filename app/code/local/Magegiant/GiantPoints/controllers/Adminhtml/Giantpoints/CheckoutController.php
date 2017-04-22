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
 * GiantPoints Checkout Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Adminhtml_Giantpoints_CheckoutController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Change Points Ajax Action
     */
    public function applyPointAction()
    {
        $result  = array();
        $session = Mage::getSingleton('checkout/session');
        $session->setIsUsedPoint(true);
        $this->_saleRuleSession($session);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return;
    }


    protected function _saleRuleSession($session)
    {
        $ruleId   = $this->getRequest()->getParam('reward_sales_rule');
        $pointAmount = $this->getRequest()->getParam('reward_sales_point');
        $data     = array(
            'point_amount' => $pointAmount,
            'rule_id'      => $ruleId
        );
        $session->setRewardSalesRules($data);
    }

    /**
     * @param $session
     */

    protected function _checkedRuleSession($session)
    {
        $checkedRules = $session->getRewardCheckedRules();
        if (!is_array($checkedRules)) {
            $checkedRules = array();
        }
        $ruleId = $this->getRequest()->getParam('rule_id');
        if ($ruleId) {
            if ($isUsed = $this->getRequest()->getParam('is_used')) {
                $data                  = array('rule_id' => $ruleId, 'use_point' => null);
                $checkedRules[$ruleId] = $data;
            } elseif (isset($checkedRules[$ruleId])) {
                unset($checkedRules[$ruleId]);
            }
            $session->setRewardCheckedRules($checkedRules);
        }
    }

    /**
     *
     */
    public function checkboxRuleAction()
    {
        Mage::getSingleton('checkout/session')->setUsePoint(true);
    }

	protected function _isAllowed()
	{
		return true;
	}
}
