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
 * Giantpoints Adminhtml Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Adminhtml_Giantpoints_Refer_SalesruleController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('giantpoints/referral')
			->_addBreadcrumb(
				Mage::helper('adminhtml')->__('Refer Friends'),
				Mage::helper('adminhtml')->__('Refer Friends')
			);

		return $this;
	}

	/**
	 * view and edit item action
	 */
	public function editAction()
	{
		$ruleId = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('giantpointsrefer/salesrule')->load($ruleId);

		if ($model->getId() || $ruleId == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			Mage::register('refer_salesrule_data', $model);

			$this->loadLayout();

			$this->_setActiveMenu('giantpoints/refer');

			$this->_addBreadcrumb(
				Mage::helper('adminhtml')->__('Refer Friends Rule'),
				Mage::helper('adminhtml')->__('Refer Friends Rule')
			);
			$this->_title($this->__('Reward Points'))
				->_title($this->__('Refer Friends'));
			if ($model->getId()) {
				$this->_title($this->__('Edit Refer Friend #%s', $model->getId()));
			} else {
				$this->_title($this->__('New Refer Friend'));
			}
			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true)
				->addItem('js', 'tiny_mce/tiny_mce.js')
				->addItem('js', 'mage/adminhtml/wysiwyg/tiny_mce/setup.js')
				->addJs('mage/adminhtml/browser.js')
				->addJs('prototype/window.js')
				->addJs('lib/flex.js')
				->addJs('mage/adminhtml/flexuploader.js');
			$this->_addContent($this->getLayout()->createBlock('giantpointsrefer/adminhtml_salesrule_edit'))
				->_addLeft($this->getLayout()->createBlock('giantpointsrefer/adminhtml_salesrule_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(
				Mage::helper('giantpointsrefer')->__('Item does not exist')
			);
			$this->_redirect('*/*/');
		}
	}

	public function newAction()
	{
		$this->_forward('edit');
	}


	/**
	 * index action
	 */
	public function indexAction()
	{
		$this->_title($this->__('Reward Points'))
			->_title($this->__('Refer Friends'));
		$this->_initAction()
			->renderLayout();
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			try {
				$redirectBack = $this->getRequest()->getParam('back', false);
				$data         = $this->_filterDates($data, array('from_date', 'to_date'));
				if (isset($data['from_date']) && $data['from_date'] instanceof Zend_Date) {
					$data['from_date'] = $data['from_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
				}
				if (isset($data['to_date']) && $data['to_date'] instanceof Zend_Date) {
					$data['to_date'] = $data['to_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
				}
				if (!empty($data['from_date']) && !empty($data['to_date'])) {
					$fromDate = new Zend_Date($data['from_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);
					$toDate   = new Zend_Date($data['to_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);

					if ($fromDate->compare($toDate) === 1) {
						throw new Exception($this->__("'To Date' must be equal or more than 'From Date'"));
					}
				}

				$model = Mage::getModel('giantpointsrefer/salesrule');
				if (isset($data['rule'])) {
					$rules = $data['rule'];
					if (isset($rules['conditions'])) {
						$data['conditions'] = $rules['conditions'];
					}
					if (isset($rules['actions'])) {
						$data['actions'] = $rules['actions'];
					}
					unset($data['rule']);
				}
				$model->loadPost($data);

				if ($this->getRequest()->getParam('_save_as_flag')) {
					$model->setId(null);
				}

				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('giantpoints')->__('Refer was successfully saved')
				);
				if ($redirectBack) {
					$this->_redirect(
						'*/*/edit', array(
							'id'       => $model->getId(),
							'_current' => true
						)
					);

					return;
				}

				return $this->_redirect('*/*/');

			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setPageData($data);
				$this->_redirect('*/*/edit', array(
					'id' => $this->getRequest()->getParam('rule_id'),
				));

				return;
			}
		}
		$this->_redirect('*/*/');

		return;
	}

	public function gridAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * mass delete action
	 */
	public function massDeleteAction()
	{
		try {
			$rates = $this->getRequest()->getParam('rule_ids');
			foreach ($rates as $rate) {
				Mage::getModel('giantpointsrefer/salesrule')->load($rate)->delete();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('giantpoints')->__('Total of %d record(s) were successfully removed', count($rates))
			);
		} catch (Exception $exc) {
			Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
		}
		$this->_redirect('*/*/index');
	}

	public function massActivateAction()
	{
		$success = 0;
		$ruleIds = $this->getRequest()->getParam('rule_ids');
		$rules   = Mage::getModel('giantpointsrefer/salesrule')->getCollection()
			->addFieldToFilter('rule_id', array('in' => $ruleIds));
		foreach ($rules as $rule) {
			try {
				$rule->setIsActive(1)
					->save();
				$success++;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(
			Mage::helper('giantpoints')->__('Total of %d record(s) were successfully updated', $success)
		);
		$this->_redirect('*/*/index');
	}

	public function massDeactivateAction()
	{
		$success = 0;
		$ruleIds = $this->getRequest()->getParam('rule_ids');
		$rules   = Mage::getModel('giantpointsrefer/salesrule')->getCollection()
			->addFieldToFilter('rule_id', array('in' => $ruleIds));
		foreach ($rules as $rule) {
			try {
				$rule->setIsActive(0)
					->save();
				$success++;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(
			Mage::helper('giantpoints')->__('Total of %d record(s) were successfully updated', $success)
		);
		$this->_redirect('*/*/index');
	}

	/**
	 * delete item action
	 */
	public function deleteAction()
	{
		if ($this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('giantpointsrefer/salesrule');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Refer rule was successfully deleted')
				);
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('giantpoints/referral');
	}
}