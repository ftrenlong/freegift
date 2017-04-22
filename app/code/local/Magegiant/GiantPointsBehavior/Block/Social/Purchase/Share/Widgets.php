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
class Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Widgets extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_product = null;
    protected $_widgetName = 'giantpointsSocialWidgetHover';
    protected $_widgetClass = 'giantpoints-social-widgets';
    protected $_widgetNotificationClass = 'giantpoints-notification';

    protected function _toHtml()
    {
        $html       = parent::_toHtml();
        $widgetName = $this->getWidgetName();
        $productId  = $this->getProduct() ? $this->getProduct()->getId() : null;
        $orderId    = $this->hasOrderId() ? $this->getOrderId() : null;
        if ($html != '') {
            $html .= "
                <script type='text/javascript'>
                    Event.observe(document, 'dom:loaded', function() {
                        " . $widgetName . ".data = " . $widgetName . ".data || {};
                        " . $widgetName . ".data.productId = '{$productId}';
                         " . $widgetName . ".data.orderId = '{$orderId}';
                    });
                </script>
            ";
        }

        return $html;
    }

    public function getWidgetClass()
    {
        $widgetClass = $this->_widgetClass;
        if ($this->getProduct()) {
            $widgetClass .= ' ' . $this->getProduct()->getId();
        }

        return $widgetClass;
    }

    public function getWidgetName()
    {
        $widgetName = $this->_widgetName;
        if ($this->getProduct()) {
            $widgetName .= $this->getProduct()->getId();
        }

        return $widgetName;
    }

    public function getWidgetNotificationClass()
    {
        $widgetNotificationClass = $this->_widgetNotificationClass;
        if ($this->getProduct()) {
            $widgetNotificationClass .= $this->getProduct()->getId();
        }

        return $widgetNotificationClass;
    }

    public function getProduct()
    {
        $this->_product = $this->getData('product');
        return $this->_product;
    }

    /**
     *
     * @deprecated. Moved to widget.css
     * @return string
     */
    public function getInlineStyling()
    {
        return '';
    }
}
