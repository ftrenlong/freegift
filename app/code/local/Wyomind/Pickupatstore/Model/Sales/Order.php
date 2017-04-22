<?php

class Wyomind_Pickupatstore_Model_Sales_Order extends Mage_Sales_Model_Order
{

    const XML_PATH_EMAIL_GUEST_TEMPLATE_PICKUPATSTORE = "pickupatstore/email/order_guest";
    const XML_PATH_EMAIL_TEMPLATE_PICKUPATSTORE = "pickupatstore/email/order";
    const XML_PATH_EMAIL_GUEST_TEMPLATE_PICKUPATSTORE_ENABLED = "pickupatstore/email/order_guest_enabled";
    const XML_PATH_EMAIL_TEMPLATE_PICKUPATSTORE_ENABLED = "pickupatstore/email/order_enabled";

    public function queueNewOrderEmail($forceMode = false)
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                    ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        /*         * * START WYOMIND CUSTOMIZATIONS * */
        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        $usePasOrderGuestTemplate = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE_PICKUPATSTORE_ENABLED, $storeId);
        $usePasOrderTemplate = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_PICKUPATSTORE_ENABLED, $storeId);
        $usePasTemplate = $usePasOrderGuestTemplate || $usePasOrderTemplate;
     
        if (stripos($this->getShippingMethod(), 'pickupatstore') !== FALSE && $usePasTemplate) {
            // Retrieve corresponding email template id and customer name
            if ($this->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE_PICKUPATSTORE, $storeId);
                $customerName = $this->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_PICKUPATSTORE, $storeId);
                $customerName = $this->getCustomerName();
            }
        } else {

            // Retrieve corresponding email template id and customer name
            if ($this->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
                $customerName = $this->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
                $customerName = $this->getCustomerName();
            }
        }

        /*         * * END  WYOMIND CUSTOMIZATIONS * */

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }
 
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
            'order' => $this,
            'billing' => $this->getBillingAddress(),
            'payment_html' => $paymentBlockHtml
            )
        );

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
                ->setEntityType(self::ENTITY)
                ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
                ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

}
