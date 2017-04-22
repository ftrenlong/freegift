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
 * @package     MageGiant_GiantPointsRefer
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsRefer Status Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsRefer
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Salesrule_Simple_Action_Options_Earning extends Varien_Object
{
    const TYPE_FIXED = 'fixed';
    const TYPE_BY_PRICE = 'by_price';

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::TYPE_FIXED    => Mage::helper('giantpointsrefer')->__('Give fixed X points to Customers'),
            self::TYPE_BY_PRICE => Mage::helper('giantpointsrefer')->__('Give X points for every Y amount of Price'),
        );
    }

    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $options;
    }

    public function toOptionArray()
    {
        return self::getOptionHash();
    }

}