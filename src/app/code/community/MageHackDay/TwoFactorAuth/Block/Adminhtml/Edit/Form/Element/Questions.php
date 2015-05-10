<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit_Form_Element_Questions extends Varien_Data_Form_Element_Abstract
{
    /**
     * Retrieve element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        /** @var $block MageHackDay_TwoFactorAuth_Block_Adminhtml_Questions */
        $block = Mage::getSingleton('core/layout')
            ->createBlock('twofactorauth/adminhtml_questions')
            ->setId($this->getHtmlId() . '_content');
        $html = '<div id="qr" class="qr-edit">'.$block->toHtml().'</div>';
        return $html;
    }
}
