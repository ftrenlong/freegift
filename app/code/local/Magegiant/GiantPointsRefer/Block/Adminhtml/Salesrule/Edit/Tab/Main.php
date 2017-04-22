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
class Magegiant_GiantPointsRefer_Block_Adminhtml_Salesrule_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('refer_salesrule_data');
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } else if ($model) {
            $data = $model->getData();
        }
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $helper = Mage::helper('giantpoints');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('General Information')));

        if ($model && $model->getId()) {
            $fieldset->addField(
                'rule_id',
                'hidden',
                array(
                    'name' => 'rule_id',
                )
            );
        }
        $fieldset->addField(
            'name',
            'text',
            array(
                'name'     => 'name',
                'label'    => $helper->__('Title'),
                'title'    => $helper->__('Title'),
                'required' => true,
            )
        );

        $fieldset->addField(
            'description',
            'editor',
            array(
                'name'  => 'description',
                'label' => Mage::helper('giantpointsrefer')->__('Description'),
                'title' => Mage::helper('giantpointsrefer')->__('Description'),
                //                'wysiwyg' => true,
            )
        );
        $fieldset->addField(
            'is_active',
            'select',
            array(
                'label'   => $helper->__('Status'),
                'title'   => $helper->__('Status'),
                'name'    => 'is_active',
                'options' => array(
                    '1' => $helper->__('Active'),
                    '0' => $helper->__('Inactive'),
                ),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'website_ids',
                'multiselect',
                array(
                    'name'               => 'website_ids[]',
                    'label'              => Mage::helper('giantpointsrefer')->__('Websites'),
                    'title'              => Mage::helper('giantpointsrefer')->__('Websites'),
                    'required'           => true,
                    'values'             => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
                    'after_element_html' => $helper->addSelectAll('rule_website_ids'),
                )
            );
        } else {
            $fieldset->addField(
                'website_ids',
                'hidden',
                array(
                    'name'  => 'website_ids[]',
                    'value' => Mage::app()->getStore(true)->getWebsiteId()
                )
            );
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }

        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        if (is_array($customerGroups)) {
            foreach ($customerGroups as $key => $group) {
                if ($group['value'] == 0) {
                    unset($customerGroups[$key]);
                }
            }
        }
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            array(
                'name'               => 'customer_group_ids[]',
                'label'              => Mage::helper('salesrule')->__('Customer Groups'),
                'title'              => Mage::helper('salesrule')->__('Customer Groups'),
                'required'           => true,
                'values'             => $customerGroups,
                'after_element_html' => $helper->addSelectAll('rule_customer_group_ids'),
            )
        );
        $fieldset->addField(
            'referrals_group_ids',
            'multiselect',
            array(
                'name'               => 'referrals_group_ids[]',
                'label'              => Mage::helper('salesrule')->__('Referral\'s Groups'),
                'title'              => Mage::helper('salesrule')->__('Referral\'s Groups'),
                'required'           => true,
                'values'             => $customerGroups,
                'after_element_html' => $helper->addSelectAll('rule_referrals_group_ids'),
            )
        );
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            array(
                'name'         => 'from_date',
                'label'        => Mage::helper('salesrule')->__('From Date'),
                'title'        => Mage::helper('salesrule')->__('From Date'),
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormatIso,
            )
        );
        $fieldset->addField(
            'to_date',
            'date',
            array(
                'name'         => 'to_date',
                'label'        => Mage::helper('salesrule')->__('To Date'),
                'title'        => Mage::helper('salesrule')->__('To Date'),
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormatIso,
            )
        );

        $afterPriorityHtml = '<p class="note"><span >'
            . $this->__("Higher priority Rule will be applied first")
            . '</span></p> ';

        $fieldset->addField(
            'sort_order',
            'text',
            array(
                'name'               => 'sort_order',
                'label'              => $helper->__('Priority'),
                'title'              => $helper->__('Priority'),
                'class'              => "validate-zero-or-greater",
                'after_element_html' => $afterPriorityHtml,
            )
        );
        $fieldset->addField(
            'save_as_flag',
            'hidden',
            array(
                'name'  => '_save_as_flag',
                'value' => 0,
            )
        );

        $form->setValues($data);
        $this->setForm($form);

        Mage::dispatchEvent('giantpointsrefer_adminhtml_salesrule_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}