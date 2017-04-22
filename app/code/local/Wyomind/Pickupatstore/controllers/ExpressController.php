<?php

require_once(Mage::getModuleDir('controllers', 'Mage_Paypal') . DS . 'ExpressController.php');

class Wyomind_Pickupatstore_ExpressController extends Mage_Paypal_ExpressController
{

    public function saveShippingMethodAction()
    {
        try {

            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));

            /* PICKUP@STORE CUSTOMIZATIONS */
            $shippineMethod=$this->getRequest()->getParam('shipping_method');
            if (stristr($shippineMethod, "pickupatstore")) {

                $pos_id = substr($shippineMethod, stripos($shippineMethod, '_') + 1);
                $data = Mage::getModel('pointofsale/pointofsale')->getPlace($pos_id)->getFirstItem()->getData();


                $shipping['firstname'] = "Store Pickup";
                $shipping['lastname'] = $data['name'];
                $shipping['company'] = '';
                $shipping['city'] = $data['city'];
                $shipping['postcode'] = $data['postal_code'];
                $shipping['country_id'] = $data['country_code'];
                $shipping['region_id'] = Mage::getModel('directory/region')->loadByCode($data['state'], $data['country_code'])->getRegionId();
                $shipping['region'] = Mage::getModel('directory/region')->loadByCode($data['state'], $data['country_code'])->getName();
                $shipping['telephone'] = $data['main_phone'];

                $shipping['street'] = array($data['address_line_1'], $data['address_line_2']);

                $shipping['same_as_billing'] = 0;
                $onepage=Mage::getSingleton('checkout/type_onepage');
                $onepage->saveShipping($shipping, false);

            }
            /* PICKUP@STORE CUSTOMIZATIONS */

            if ($isAjax) {
                $this->loadLayout('paypal_express_review_details');
                $quote=$this->_getCheckoutSession()->getQuote();
                $this->getResponse()->setBody(
                    $this->getLayout()->getBlock('root')
                                ->setQuote($quote)
                    ->toHtml()
                );
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to update shipping method.'));
            Mage::logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody(
                '<script type="text/javascript">window.location.href = '
                . Mage::getUrl('*/*/review') . ';</script>'
            );
        } else {
            $this->_redirect('*/*/review');
        }
    }

}
