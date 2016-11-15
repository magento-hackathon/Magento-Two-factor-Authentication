<?php

class MageHackDay_TwoFactorAuth_Helper_Data extends Mage_Core_Helper_Data
{
    public function isActive()
    {
        return Mage::getStoreConfigFlag('admin/security/active');
    }

    public function isForceForBackend()
    {
        return Mage::getStoreConfigFlag('admin/security/force_for_backend');
    }

    public function isFrontendActive()
    {
        return Mage::getStoreConfigFlag('admin/security/frontend_active');
    }

    public function getRememberMeDuration()
    {
        return Mage::getStoreConfig('admin/security/remember_me_duration');
    }

    public function getAllowIps()
    {
        $allowed = array();
        $ips = Mage::getStoreConfig('admin/security/allow_ips');
        if (!empty($ips)) {
            $ips = array_filter(explode(',', $ips));
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (strpos($ip, '/')) {
                    list($subnet, $prefix) = explode('/', $ip);
                    if (empty($subnet) || empty($prefix) || $prefix > 32 || $prefix < 0) {
                        continue;
                    }

                    $ipCount = 1 << (32 - $prefix);
                    $start = ip2long($subnet);
                    for ($i = 0; $i < $ipCount; $i++) {
                        $allowed[] = long2ip($start + $i);
                    }
                } else {
                    $allowed[] = $ip;
                }
            }
        }

        return $allowed;
    }
}