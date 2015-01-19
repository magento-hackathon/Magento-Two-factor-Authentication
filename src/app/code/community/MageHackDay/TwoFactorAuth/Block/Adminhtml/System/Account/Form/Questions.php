<?php

/**
 * Secret questions form element
 */
class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Form_Questions extends Varien_Data_Form_Element_Abstract
{
    /**
     * @var Mage_Adminhtml_Block_Template
     */
    protected $_block;

    /**
     * Retrieve block for the form element
     *
     * @return Mage_Adminhtml_Block_Template
     */
    protected function _getBlock()
    {
        if ( ! $this->_block) {
            $this->_block = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/template')
                ->setTemplate('twofactor/questions.phtml')
                ->setId($this->getHtmlId() . '_content')
                ->setElement($this);
        }
        return $this->_block;
    }

    /**
     * Retrieve element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $block = $this->_getBlock();
        $html = $block->toHtml();
        return $html;
    }

    /**
     * Retrieve field name
     *
     * @param int $index
     * @param string $field
     * @return string
     */
    public function getFieldName($index = NULL, $field = NULL)
    {
        $name = $this->getData('name');
        if ($formSuffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $formSuffix);
        }
        if ( ! is_null($index)) $name .= '['.$index.']';
        if ( ! empty($field)) $name .= '['.$field.']';
        return $name;
    }

    /**
     * Retrieve the form element HTML
     *
     * @return string
     */
    public function toHtml()
    {
        return '<tr><td>' . $this->getElementHtml() . '</td></tr>';
    }
}
