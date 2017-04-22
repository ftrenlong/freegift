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
 * Giantpoints Adminhtml Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsBehavior_Block_Adminhtml_Behavior extends Mage_Adminhtml_Block_System_Config_Edit
{


    public function __construct()
    {
        parent::__construct();
        $sections       = Mage::getSingleton('giantpointsbhv/config')->getSections();
        $this->_section = $sections->giantpoints;
        $this->setHeaderCss((string)$this->_section->header_css);
        $this->setTitle(Mage::helper('giantpoints')->__('Customer behavior earning'));
    }

    /**
     * init Form
     *
     */
    public function initForm()
    {
        $blockName = 'giantpointsbhv/adminhtml_form';
        $this->setChild('form', $this->getLayout()->createBlock($blockName)->initForm());

        return $this;
    }


}
