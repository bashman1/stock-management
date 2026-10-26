<?php

namespace App\Http\Controllers;

use App\Models\LicensePackagePrice;
use Illuminate\Http\Request;

class LicensePackagePriceController extends Controller
{
    public function createLicensePackagePrice(Request $request)
    {
        try {
            $licensePackagePrice = LicensePackagePrice::create([
                'license_package_id' => $request->license_package_id,
                'price' => $request->price,
            ]);
            return $this->genericResponse(true, 'License Package Price created successfully', 201, $licensePackagePrice, 'createLicensePackagePrice', $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createLicensePackagePrice", $request);
        }
    }

    public function getLicensePackagePrices()
    {
        try {
            $prices = LicensePackagePrice::all();
            return $this->genericResponse(true, 'License Package Prices fetched successfully', 200, $prices, 'getLicensePackagePrices', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getLicensePackagePrices", []);
        }
    }
}
