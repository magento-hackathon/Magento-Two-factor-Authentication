<?php

class MageHackDay_TwoFactorAuth_Test_Controller_InterstitialControllerTest extends EcomDev_PHPUnit_Test_Case_Controller
{

  /**
   * @test
   * @return void
   */
  public function testDoesRedirectWorkAction()
  {
    $feActive = Mage::getStoreConfig('admin/security/frontend_active');

    if (empty($feActive))
    {
      return;
    }

    //TODO: test redirect after login
  }

}