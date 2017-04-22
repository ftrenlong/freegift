<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Total Point Earn/Spend Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Totals_Creditmemo_Form_Added extends Magegiant_GiantPoints_Block_Adminhtml_Totals_Abstract
{
    protected $_currentPoint;

    public function __construct()
    {
        $this->_currentPoint = new Varien_Object();
        $this->getMaxSpentPointRefund();
        $this->getMaxRateEarnedRefund();
        $this->getMaxInviteeEarnedRefund();
        $this->getMaxReferralEarnedRefund();
    }


    /**
     * get points that spent for order
     *
     * @return int
     */
    public function getCurrentPoint()
    {
        return $this->_currentPoint;
    }

}
