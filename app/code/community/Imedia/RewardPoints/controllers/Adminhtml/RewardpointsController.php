<?php
/**
 * Admin manage Reward points controller
 */
class Imedia_RewardPoints_Adminhtml_RewardpointsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     *
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_title($this->__('Reward Points'));
        
        $this->loadLayout()
            ->_setActiveMenu('catalog/reward_points')
            ->_addBreadcrumb(Mage::helper('imedia_rewardpoints')->__('Reward Points')
                    , Mage::helper('imedia_rewardpoints')->__('Reward Points'));
        return $this;
    }
    
    /**
     * Index action method
     */
    public function indexAction() 
    {
        $this->_initAction();
        $this->renderLayout();
    }
    
    /**
     * Used for Ajax Based Grid
     *
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('imedia_rewardpoints/adminhtml_rewardpoints_grid')->toHtml()
        );
    }
	public function exportCsvAction()
	{
		$fileName   = 'rewardpoints.csv';
		$content    = $this->getLayout()->createBlock('imedia_rewardpoints/adminhtml_rewardpoints_grid')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
  
}