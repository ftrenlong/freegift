<?php

class Magegiant_GiantPointsBehavior_Block_Social_Purchase_Share_Items extends Mage_Checkout_Block_Onepage_Success
{
    const COLUMN_COUNT = 3;
    protected static $_widgetsNodes = null;

    public function getOrderItemsCollection()
    {
        $items = $this->_getOrder()->getAllVisibleItems();

        return $items;
    }

    public function getOrderedProduct($item)
    {
        return Mage::getModel('catalog/product')->load($item->getProductId());
    }

    public function getColumnCount()
    {
        return self::COLUMN_COUNT;
    }

    public function getSocialWidgetsHtml($product)
    {
        $widgets = $this->getLayout()->getBlock('giantpoints.checkout.purchase.share.widgets')
            ->setData('product', $product)
            ->setData('order_id', $this->_getOrder()->getId());
        $html    = $widgets->toHtml();

        return $html;
    }

    protected function _getOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
    }

    /**
     * Wrapper for standart strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool   $escape
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $escape = false)
    {
        $result = strip_tags($data, $allowableTags);

        return $escape ? $this->escapeHtml($result, $allowableTags) : $result;
    }
}
