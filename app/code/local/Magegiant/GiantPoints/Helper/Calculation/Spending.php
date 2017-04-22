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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Calculation_Spending extends Magegiant_GiantPoints_Helper_Calculation_Abstract
{
    public function dispatchEvent($name, $container)
    {
        Mage::dispatchEvent($name, array(
            'container' => $container,
        ));
    }

    /**
     * get total points spent
     *
     * @return int
     */
    public function getTotalSpendingPoints($quote = null)
    {
        if (!$quote)
            $quote = $this->getQuote();
        $address   = $this->getAddress($quote);
        $container = $this->_createObject();
        $container->setData('total_point_spent', $address->getGiantpointsSpent());
        $this->dispatchEvent('giantpoints_conversion_spending_get_total_point', $container);

        return $container->getData('total_point_spent');
    }

    protected function _createObject($data = array())
    {
        return new Varien_Object($data);
    }


    /**
     * get Checked rule discount
     *
     * @return float
     */
    public function getCheckedRuleSpentDiscount()
    {
        $container = $this->_createObject();
        $container->setData('checked_rule_discount', 0);
        $this->dispatchEvent('giantpoints_conversion_spending_checked_rule_discount', $container);

        return $container->getData('checked_rule_discount');
    }

    /**
     *
     * @return int
     */
    public function getCheckedRuleSpentPoint()
    {
        $container = $this->_createObject();
        $container->setData('checked_rule_point', 0);
        $this->dispatchEvent('giantpoints_conversion_spending_checked_rule_point', $container);

        return $container->getData('checked_rule_point');
    }

}
