<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form select element
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Form_Element_Hours extends Varien_Data_Form_Element_Abstract
{

    /**
     * Init Element
     *
     * @param array $attributes
     */
    public function __construct($attributes = array()) 
    {
        parent::__construct($attributes);
        $this->setType('checkbox');
        $this->setExtType('checkbox');
    }

    /**
     * Retrieve allow attributes
     *
     * @return array
     */
    public function getHtmlAttributes() 
    {
        return array('type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled');
    }

    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepareValues() 
    {
        $options = array();
        $values = array(
            array(
                'value' => "Monday",
                'label' => Mage::helper('pointofsale')->__('Monday'),
            ),
            array(
                'value' => "Tuesday",
                'label' => Mage::helper('pointofsale')->__('Tuesday'),
            ),
            array(
                'value' => "Wednesday",
                'label' => Mage::helper('pointofsale')->__('Wednesday'),
            ),
            array(
                'value' => "Thursday",
                'label' => Mage::helper('pointofsale')->__('Thursday'),
            ),
            array(
                'value' => "Friday",
                'label' => Mage::helper('pointofsale')->__('Friday'),
            ),
            array(
                'value' => "Saturday",
                'label' => Mage::helper('pointofsale')->__('Saturday'),
            ),
            array(
                'value' => "Sunday",
                'label' => Mage::helper('pointofsale')->__('Sunday'),
            ),
        );

        return $values;
    }

    /**
     * Retrieve HTML
     *
     * @return string
     */
    public function getElementHtml() 
    {
        $values = $this->_prepareValues();

        if (!$values) {
            return '';
        }
        $id = $this->getHtmlId();

        $html = "<script language='javascript'>var elementId = '" . $id . "';</script>";
        
        $html.= '<ul class="checkboxes">';


        foreach ($values as $day) {

            $html.='<li style="width:250px;border:1px dotted grey;padding:4px">';
            $html .= '<input value="' . $day['value'] . '" class="' . $id . '_day" id="' . $day['value'] . '" onclick="PointOfSale.activeField(this)" type="checkbox" /> ';
            $html .= '<label for="' . $id . '"><b>' . $day['label'] . '</b></label>';


            $html.="<div style='margin:4px 0 2px 35px;'> <select style='width:60px;' id='" . $day['value'] . "_open' onchange='PointOfSale.summary()'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html.="<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html.="</select> - ";
            $html.="<select style='width:60px;' id='" . $day['value'] . "_close' onchange='PointOfSale.summary()'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html.="<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html.="</select></div>";
            
            
            $html .= '<input value="' . $day['value'] . '" class="' . $id . '_lunch" id="' . $day['value'] . '_lunch" onclick="PointOfSale.activeLunchTime(this)" type="checkbox"/>';
            $html .= '<label for="' . $day['value'] . '_lunch"><b>&nbsp;' . Mage::helper('pointofsale')->__('Lunch time') . '</b></label>';
            $html.="<div style='margin:4px 0 2px 35px;'> <select style='width:60px;' id='" . $day['value'] . "_lunch_open' onchange='PointOfSale.summary()'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html.="<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html.="</select> - ";
            $html.="<select style='width:60px;' id='" . $day['value'] . "_lunch_close' onchange='PointOfSale.summary()'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html.="<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html.="</select></div>";

            $html.='</li>';
        }
        $html .= '</ul>'
                . $this->getAfterElementHtml();

        return $html;
    }

}
