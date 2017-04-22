<?php

class Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Widgets_Twitter extends Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Widgets
{
    public function isEnabled()
    {
        if (Mage::helper('giantpointsbhv/config')->isEnabledPurchaseShareTwitter()) {
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

    public function showButtonCount()
    {
        $countEnabled = Mage::helper('giantpointsbhv/config')->isShowTwitterCount();

        return $countEnabled;
    }

    public function getTweetedUrl()
    {
        $product = $this->getProduct();

        return Mage::helper('giantpoints')->getProductUrl($product);
    }
}
