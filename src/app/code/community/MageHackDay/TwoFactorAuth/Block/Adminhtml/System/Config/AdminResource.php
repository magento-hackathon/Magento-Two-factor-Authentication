<?php
/**
 * Populate select element with list of resource id
 *
 * @category    MageHackDay
 * @package     MageHackDay_TwoFactorAuth
 * @author      William Tran <william@aligent.com.au>
 */
class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Config_AdminResource extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $oProtectedResourceSelect;

    protected function getProtectedResourceSelect() {
        $oRoles = Mage::getModel("admin/roles");
        $aOptions = array();
        foreach ($oRoles->getResourcesList() as $vResourceId => $aResource) {
            $aOptions[] = array(
                'label' => $vResourceId,
                'value' => $vResourceId
            );
        }
        $this->setOptions($aOptions);
        if (!$this->oProtectedResourceSelect) {
            $this->oProtectedResourceSelect = Mage::app()->getLayout()->createBlock('twofactorauth/adminhtml_select')->setIsRenderToJsTemplate(true);
            $this->oProtectedResourceSelect->setOptions($aOptions);
            $this->oProtectedResourceSelect->setExtraParams('style="width:250px;"');
        }
        return $this->oProtectedResourceSelect;

    }

    public function _prepareToRender()
    {
        $this->oProtectedResourceSelect = null;
        $this->addColumn('resource_id', array(
            'label' => Mage::helper('twofactorauth')->__('Resource Id'),
            'renderer'=> $this->getProtectedResourceSelect(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('twofactorauth')->__('Add Protected Resource');
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->getProtectedResourceSelect()->calcOptionHash(
                $row->getResourceId()),
            'selected="selected"'
        );
    }

}