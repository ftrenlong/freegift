<?php

class Wyomind_Pickupatstore_Model_Observer
{
    function orderUpdate($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (strstr($order->getShippingMethod(), "pickupatstore")) {
            $storeId = substr($order->getShippingMethod(), stripos($order->getShippingMethod(), '_') + 1);
            $store = Mage::getModel('pointofsale/pointofsale')->getPlace($storeId)->getFirstItem();

            $storeDetails = $store->getName() . ' ';
            $storeDetails.=" [ ";
            $o = 0;
            if ($store->getAddress_line_1()) {
                $storeDetails.=$store->getAddress_line_1();
                $o++;
            }
            if ($store->getAddress_line_2()) {
                if ($o) {
                    $storeDetails.=", ";
                }
                $storeDetails.=$store->getAddress_line_2();
                $o++;
            }
            if ($store->getCity()) {
                if ($o) {
                    $storeDetails.=", ";
                }
                $storeDetails.=$store->getCity();
                $o++;
            }
            if ($store->getState()) {
                if ($o) {
                    $storeDetails.=", ";
                }
                $storeDetails.=$store->getState();
                $o++;
            }
            //if($store->getPostalCode())$storeDetails.=$store->getPostalCode()." ";
            $storeDetails .= " ]";

            $storeDetails .= "<br>";

            $storeDetails .= "<br>";
            $template = "<b>{day}</b> {H1}:{mn1} - {H2}:{mn2}<br>";
            $storeDetails .= Mage::helper('pointofsale')->getHours($store->getHours(), $template);
            if (Mage::getStoreConfig('carriers/pickupatstore/dropdown') && $order->getPickupDatetime()) {
                if (Mage::getStoreConfig('carriers/pickupatstore/time')) {
                    $storeDetails.="<br>" . Mage::helper('pickupatstore')->__('Your pickup time: ')
                            . Mage::helper('pickupatstore')->formatDatetime($order->getPickupDatetime());
                } elseif (Mage::getStoreConfig('carriers/pickupatstore/date')) {
                    $storeDetails.="<br>" . Mage::helper('pickupatstore')->__('Your pickup date: ')
                            . Mage::helper('pickupatstore')->formatDate($order->getPickupDatetime());
                }
            }
            $order->setPickupPointofsaleId($store->getPlaceId());
            $order->setShippingDescription($storeDetails)->save();

        }

        return;
    }

    function shippingUpdate($observer)
    {
        if (Mage::getStoreConfig('carriers/pickupatstore/date')) {
            $quote = $observer->getEvent()->getQuote();
            $request = $observer->getEvent()->getRequest()->getPost();

            $time = null;
            if (isset($request['pickup_hour'])) {
                $time = strtotime($request['pickup_day'] . ' ' . $request['pickup_hour'] . ":00");
            } else if (isset($request['pickup_day'])) {
                $time = strtotime($request['pickup_day'] . '00:00:00');
            }
            $quote->setPickupDatetime(Mage::getSingleton('core/date')->date($time))->save();

            $idPosition = strpos($request['shipping_method'], "pickupatstore_") + 14;
            $pointofsaleId = substr($request['shipping_method'], $idPosition);

            $quote->setPickupPointofsaleId($pointofsaleId)->save();
        }
    }

    public function addColumn(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;
        $class = Mage::getStoreConfig("pickupatstore/settings/grid");

        if (is_a($block, $class)) {
            $block->addColumnAfter(
                'pickup_datetime', array(
                'header' => Mage::helper('sales')->__('Pickup Date/time'),
                'index' => 'pickup_datetime',
                'type' => 'datetime',
                'width' => '150px',
                    ), 'status'
            );

            $pointofsalesCollection = Mage::getSingleton('pointofsale/pointofsale')->getCollection();
            $pointofsaleId = Mage::app()->getRequest()->getParam('place_id');

            if (null !== $pointofsaleId) {
                $pointofsalesCollection->addFieldToFilter('place_id', $pointofsaleId);
            }

            foreach ($pointofsalesCollection as $pointofsale) {
                $pointofsales[$pointofsale->getPlaceId()] = $pointofsale->getName();
            }

            $block->addColumnAfter(
                'pickup_pointofsale_id', array(
                'header' => Mage::helper('sales')->__('Pickup at store'),
                'index' => 'pickup_pointofsale_id',
                'type' => 'options',
                'width' => '150px',
                'options' => $pointofsales,
                'renderer' => 'Wyomind_Pickupatstore_Block_Adminhtml_Sales_Order_Renderer_Pointofsale',
                    ), 'status'
            );
        }

        return $observer;
    }

}
