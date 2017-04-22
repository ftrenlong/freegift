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


class Magegiant_GiantPointsRefer_Model_Salesrule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{

    public function __construct()
    {
        parent::__construct();
        $this->setType('giantpointsrefer/salesrule_condition_product_combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('giantpointsrefer/salesrule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array('value' => 'giantpointsrefer/salesrule_condition_product|' . $code, 'label' => $label);
            } else {
                $pAttributes[] = array('value' => 'giantpointsrefer/salesrule_condition_product|' . $code, 'label' => $label);
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                 array(
                     'value' => 'giantpointsrefer/salesrule_condition_product_combine',
                     'label' => Mage::helper('catalog')->__('Conditions Combination'),
                 ),
                 array(
                     'label' => Mage::helper('giantpoints')->__('Cart Item Attribute'),
                     'value' => $iAttributes,
                 ),
                 array(
                     'label' => Mage::helper('giantpoints')->__('Product Attribute'),
                     'value' => $pAttributes,
                 ),
            )
        );
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}