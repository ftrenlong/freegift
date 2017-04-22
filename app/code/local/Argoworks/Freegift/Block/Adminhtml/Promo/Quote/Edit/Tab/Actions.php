<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Argoworks_Freegift_Block_Adminhtml_Promo_Quote_Edit_Tab_Actions
 *
 * @author hoile
 */
class Argoworks_Freegift_Block_Adminhtml_Promo_Quote_Edit_Tab_Actions 
extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Actions {
    
    protected function _prepareForm() {
         parent::_prepareForm(); 
        /**
     * Get form object
     *
     * @return Varien_Data_Form
     */
         $model = Mage::registry('current_promo_quote_rule');
       $form = $this->getForm();
       // get field set of this form
       $fieldset = $form->getElement('action_fieldset');
      
      
        $fieldset->addField('free_gift_apply', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Free Gift'),
            'title'     => Mage::helper('catalogrule')->__('Free Gift'),
            'name'      => 'free_gift_apply',
            'options'   => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
        ));
         Mage::dispatchEvent('adminhtml_block_salesrule_actions_prepareform', array('form' => $form));
           $form->setValues($model->getData());
           if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

  return $this;
    }
}
