<?php

class MageHackDay_TwoFactorAuth_Test_Controller_Adminhtml_TwofactorauthControllerTest extends EcomDev_PHPUnit_Test_Case_Controller
{

  /**
   * @test
   * @return void
   */
  public function testDoesRedirectWorkAction()
  {
    $beActive = Mage::getStoreConfig('admin/security/force_for_backend');

    if (empty($beActive))
    {
      return;
    }


    //TODO: test redirect after login
  }

}