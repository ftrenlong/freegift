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

class Magegiant_GiantPointsRefer_Model_Salesrule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('giantpointsrefer/salesrule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $addressCondition = Mage::getModel('giantpointsrefer/salesrule_condition_address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = array('value' => 'giantpointsrefer/salesrule_condition_address|' . $code, 'label' => $label);
        }

        $helper = Mage::helper('giantpoints');
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                 array(
                     'value' => 'giantpointsrefer/salesrule_condition_product_found',
                     'label' => $helper->__('Product attribute combination'),
                 ),
                 array(
                     'value' => 'giantpointsrefer/salesrule_condition_product_subselect',
                     'label' => $helper->__('Products subselection'),
                 ),
                 array(
                     'value' => 'giantpointsrefer/salesrule_condition_combine',
                     'label' => $helper->__('Conditions combination'),
                 ),
                 array(
                     'value' => $attributes,
                     'label' => $helper->__('Cart Attribute'),
                 ),
            )
        );
        return $conditions;
    }
}