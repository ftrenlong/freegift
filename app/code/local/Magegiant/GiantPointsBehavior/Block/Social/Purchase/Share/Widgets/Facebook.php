<?php

class Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Widgets_Facebook extends Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Widgets
{
    public function isEnabled()
    {
        if (Mage::helper('giantpointsbhv/config')->isEnabledPurchaseShareFacebook()) {
            return parent::isEnabled();
        }

        return false;
    }

    protected function _toHtml()
    {
        if ($this->isEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    public function getOnClickAction()
    {
        $action = "fbShareAction(this, {url: '{$this->getProductUrl()}', eventName: 'facebook_product_share:response'}); return false;";

        return $action;
    }

    /**
     * Returns product's URL as configured in Magento admin.
     *
     * @return string Product URL
     */
    public function getProductUrl()
    {
        $product = $this->getProduct();

        return Mage::helper('giantpoints')->getProductUrl($product);
    }
}
