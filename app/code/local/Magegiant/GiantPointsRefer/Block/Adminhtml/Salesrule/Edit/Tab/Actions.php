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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Block_Adminhtml_Salesrule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('refer_salesrule_data');
        $form  = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $helper = Mage::helper('giantpoints');

        $fieldset     = $form->addFieldset('customer_action_fieldset', array('legend' => $helper->__('Customer Action')));
        $simpleAction = $fieldset->addField('simple_action', 'select', array(
            'label'   => Mage::helper('giantpointsrefer')->__('Action'),
            'title'   => Mage::helper('giantpointsrefer')->__('Action'),
            'name'    => 'simple_action',
            'options' => Mage::getModel('giantpointsrefer/salesrule_simple_action_options_earning')->getOptionArray(),
        ));

        $fieldset->addField('point_amount', 'text', array(
            'label'    => Mage::helper('giantpointsrefer')->__('Points (X)'),
            'title'    => Mage::helper('giantpointsrefer')->__('Points (X)'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'point_amount',
        ));
        $pointY = $fieldset->addField('money_step', 'text', array(
            'label' => Mage::helper('giantpointsrefer')->__('Money Step (Y)'),
            'title' => Mage::helper('giantpointsrefer')->__('Money Step (Y)'),
            'name'  => 'money_step',
        ));

        $fieldset       = $form->addFieldset('referral_action_fieldset', array('legend' => $helper->__('Referral\'s Action')));
        $simpleActionRf = $fieldset->addField('referrals_simple_action', 'select', array(
            'label'   => Mage::helper('giantpointsrefer')->__('Referral\'s action type'),
            'title'   => Mage::helper('giantpointsrefer')->__('Referral\'s action type'),
            'name'    => 'referrals_simple_action',
            'options' => Mage::getModel('giantpointsrefer/salesrule_simple_action_options_earning')->getOptionArray(),
        ));

        $fieldset->addField('referrals_point_amount', 'text', array(
            'label'    => Mage::helper('giantpointsrefer')->__('Referral\'s points (X)'),
            'title'    => Mage::helper('giantpointsrefer')->__('Referral\'s points (X)'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'referrals_point_amount',
        ));
        $pointYRf = $fieldset->addField('referrals_money_step', 'text', array(
            'label' => Mage::helper('giantpointsrefer')->__('Referral\'s Money Step (Y)'),
            'title' => Mage::helper('giantpointsrefer')->__('Referral\'s Money Step (Y)'),
            'name'  => 'referrals_money_step',
        ));
        $fieldset->addField(
            'stop_rules_processing',
            'select',
            array(
                'label'   => $helper->__('Stop further rules processing'),
                'title'   => $helper->__('Stop further rules processing'),
                'name'    => 'stop_rules_processing',
                'options' => array(
                    '1' => $helper->__('Yes'),
                    '0' => $helper->__('No'),
                ),
                'value'   => 1
            )
        );

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newActionHtml/form/rule_actions_fieldset'));

        $fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('giantpointsrefer')->__('Apply the rule only to cart items matching the following conditions (leave blank for all items)')))->setRenderer($renderer);

        // field dependencies
        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($simpleAction->getHtmlId(), $simpleAction->getName())
                ->addFieldMap($pointY->getHtmlId(), $pointY->getName())
                ->addFieldMap($simpleActionRf->getHtmlId(), $simpleActionRf->getName())
                ->addFieldMap($pointYRf->getHtmlId(), $pointYRf->getName())
                ->addFieldDependence(
                    $pointY->getName(),
                    $simpleAction->getName(),
                    Magegiant_GiantPointsRefer_Model_Salesrule_Simple_Action_Options_Earning::TYPE_BY_PRICE)
                ->addFieldDependence(
                    $pointYRf->getName(),
                    $simpleActionRf->getName(),
                    Magegiant_GiantPointsRefer_Model_Salesrule_Simple_Action_Options_Earning::TYPE_BY_PRICE)
        );

        $fieldset->addField('actions', 'text', array(
            'label' => Mage::helper('giantpointsrefer')->__('Apply To'),
            'title' => Mage::helper('giantpointsrefer')->__('Apply To'),
            'name'  => 'actions',
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));
        if (count($model->getData()))
            $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}