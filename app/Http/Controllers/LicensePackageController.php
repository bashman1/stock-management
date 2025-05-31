<?php

namespace App\Http\Controllers;

use App\Models\LicensePackage;
use App\Models\LicensePackageConfig;
use App\Models\LicensePackageLimitation;
use Illuminate\Http\Request;

class LicensePackageController extends Controller
{
    public function getLicensePackageConfigs(){
        try {
            $licensePackages = LicensePackageConfig::where('status', 'Active')->get();
            return $this->genericResponse(true, 'License Packages fetched successfully', 200, $licensePackages, 'getLicensePackageConfigs', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getLicensePackageConfigs", []);
        }
    }


    public function getLicensePackageLimitations(){
        try {
            $licensePackages = LicensePackageLimitation::where('status', 'Active')->get();
            return $this->genericResponse(true, 'License Packages fetched successfully', 200, $licensePackages, 'getLicensePackageLimitations', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getLicensePackageLimitations", []);
        }
    }
}
