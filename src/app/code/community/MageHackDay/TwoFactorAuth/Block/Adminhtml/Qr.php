<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Qr extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve QR code url
     *
     * @return string
     */
    public function getQrCodeUrl()
    {
        $qrCodeUrl = Mage::getSingleton('core/session')->getQrCodeUrl();
        if ( ! $qrCodeUrl) {
            $authHelper = Mage::helper('twofactorauth/auth');
            $qrCodeUrl = $authHelper->getQrCodeImageUrl($authHelper->getStoreName(), $this->getQrCodeSecret());
            Mage::getSingleton('core/session')->setQrCodeUrl($qrCodeUrl);
        }
        return $qrCodeUrl;
    }

    /**
     * Retrieve QR core secret
     *
     * @return string
     */
    public function getQrCodeSecret()
    {
        $secret = Mage::getSingleton('core/session')->getSecret();
        if ( ! $secret) {
            $secret = Mage::helper('twofactorauth/auth')->createSecret();
            Mage::getSingleton('core/session')->setSecret($secret);
        }
        return $secret;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }

    /**
     * Retrieve the list of existing questions including answers
     *
     * @return array
     */
    public function getItems()
    {
        if ( ! $this->hasData('items')) {
            $items = [];
            $questionsCollection = Mage::getResourceModel('twofactorauth/user_question_collection')->addUserFilter($this->getUser());
            $questionsCollection->setOrder('question_id', 'ASC');
            foreach ($questionsCollection as $question) { /** @var $question MageHackDay_TwoFactorAuth_Model_User_Question */
                $items[] = array(
                    'question' => $question->getQuestion(),
                    'answer'   => $question->getAnswer(),
                );
            }
            $this->setData('items', $items);
        }
        return $this->getData('items');
    }

    /**
     * Retrieve item name
     *
     * @param int $iterator
     * @param string $field
     * @return string
     */
    public function getItemName($iterator, $field)
    {
        return "questions[{$iterator}][{$field}]";
    }

    /**
     * Retrieve item value
     *
     * @param int $iterator
     * @param string $field
     * @return string
     */
    public function getItemValue($iterator, $field)
    {
        $items = $this->getItems();
        return isset($items[$iterator][$field]) ? strval($items[$iterator][$field]) : '';
    }
}