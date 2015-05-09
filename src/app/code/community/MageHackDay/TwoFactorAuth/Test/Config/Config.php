<?php

/**
 * @category Test
 * @package  MageHackDay_TwoFactorAuth
 * @author   FireGento Team <team@firegento.com>
 * @license  The MIT License (MIT)
 */
class MageHackDay_TwoFactorAuth_Test_Config_Config extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @test
     * @return void
     */
    public function codePoolIsCommunity()
    {
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     * @return void
     */
    public function moduleVersionIsSet()
    {
        $this->assertModuleVersionGreaterThan('0.0.1');
    }
}
