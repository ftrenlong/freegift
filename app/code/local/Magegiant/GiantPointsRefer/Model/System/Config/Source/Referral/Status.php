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
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_System_Config_Source_Referral_Status
{
    const REFERRAL_SIGNUP = 'signup';
    const REFERRAL_ORDER  = 'order';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::REFERRAL_SIGNUP,
                'label' => Mage::helper('giantpoints')->__('Referral Signs-up')
            ),
            array(
                'value' => self::REFERRAL_ORDER,
                'label' => Mage::helper('giantpoints')->__('Referral Places First Order')
            ),
        );
    }
}

