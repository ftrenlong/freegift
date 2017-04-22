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
class Magegiant_GiantPointsBehavior_Block_Adminhtml_System_Config_Form_Field_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl("giantpointsrule/adminhtml_system/reinstall");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(Mage::helper('giantpoints')->__('Reinstall'))
            ->setOnClick("return reinstallBehavior();")
            ->toHtml();

        $html .= "<p class='note'>";
        $html .= "<span style='color:#E02525;'>";
        $html .= Mage::helper('giantpoints')->__(
            "This action is reinstall reward points rule table"
        );
        $html .= "</span>";
        $html .= "</p>";
        $html .= "
            <script type='text/javascript'>
                function reinstallBehavior(){
                    if (confirm('" . $this->__('Are you sure to reset all reward points behavior table?') . "')) {
                        var url ='{$url}';
                        new Ajax.Request(url, {
                            parameters: {
                                         form_key: FORM_KEY,
                                         },
                            evalScripts: true,
                            onSuccess: function(transport) {
                                if(transport.responseText =='success'){
                                 alert('" . $this->__('All table removed successfully') . "');
                                }
                            }
                        });
                    }
                }
            </script>
        ";

        return $html;
    }
}