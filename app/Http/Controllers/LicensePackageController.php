<?php

namespace App\Http\Controllers;

use App\Models\LicenseBenefits;
use App\Models\LicensePackage;
use App\Models\LicensePackageBenefit;
use App\Models\LicensePackageConfig;
use App\Models\LicensePackageLimitation;
use App\Models\LicensePackagePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function getLicenseBenefits(){
        try {
            $licensePackages = LicenseBenefits::where('status', 'Active')->get();
            return $this->genericResponse(true, 'License Packages fetched successfully', 200, $licensePackages, 'getLicenseBenefits', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getLicenseBenefits", []);
        }
    }

    public function createLicensePackage(Request $request){
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:license_packages,name',
                'implementation' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);
            $data['created_by'] = $this->getUserLogin()->id;
            $data['created_on'] = now();
            $data['implementation'] = (double) $data['implementation'];

            $licensePackage = LicensePackage::create($data);

            foreach ($request->prices as $config) {
                $configData = [
                    'license_package_id' => $licensePackage->id,
                    'duration_id'=> $config['duration']['id'],
                    'amount'=> (double) $config['amount'],
                    'created_by' => $this->getUserLogin()->id,
                    'created_on' => now(),
                ];
                LicensePackagePrice::create($configData);
            }

            foreach ($request->benefits as $benefit) {
                LicensePackageBenefit::create([
                    'license_package_id' => $licensePackage->id,
                    'license_benefit_id' => $benefit['id'],
                    'created_by' => $this->getUserLogin()->id,
                    'created_on' => now(),
                ]);
            }
            DB::commit();
            return $this->genericResponse(true, 'License Package created successfully', 201, $licensePackage, 'createLicensePackage', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createLicensePackage", $request);
        }
    }


    public function getLicensePackages(){
        try {

            $queryString = "SELECT
                P.id,
                P.name AS package_name,
                P.implementation,
                P.description,
                P.status,
                P.created_by,
                P.updated_by,
                P.created_on,
                P.updated_on,
                P.created_at,
                P.updated_at,
                -- Aggregate license prices (deduplicated using DISTINCT if needed)
                COALESCE(
                    json_agg(
                     DISTINCT jsonb_build_object(
                     --jsonb_build_object(
                        'id', X.id,
                        'duration_id', X.duration_id,
                        'amount', X.amount,
                        'status', X.status,
                        'duration', D.name,
                        'created_at', X.created_at
                    )
                    ) FILTER (WHERE X.id IS NOT NULL),
                    '[]'
                ) AS license_prices,
                -- Aggregate license benefits
                COALESCE(
                    json_agg(
                    DISTINCT jsonb_build_object(
                     --jsonb_build_object(
                        'id', C.id,
                        'license_benefit_id', C.license_benefit_id,
                        'benefit', B.name,
                        'status', C.status,
                        'created_at', C.created_at
                    )
                    ) FILTER (WHERE C.id IS NOT NULL),
                    '[]'
                ) AS benefits,
                -- Full name of user
                CONCAT_WS(' ', U.first_name, U.last_name, U.other_name) AS user_name
                FROM
                license_packages P
                -- Joins
                LEFT JOIN license_package_prices X ON X.license_package_id = P.id
                LEFT JOIN durations D ON D.id = X.duration_id
                LEFT JOIN license_package_benefits C ON C.license_package_id = P.id
                LEFT JOIN license_benefits B ON B.id = C.license_benefit_id
                LEFT JOIN users U ON U.id = P.created_by
                WHERE P.status = 'Active'
                -- Grouping only by primary table and required user fields
                GROUP BY
                P.id, U.first_name, U.last_name, U.other_name
                ORDER BY P.id DESC ";

                $countSql = "SELECT COUNT(DISTINCT P.id) AS total
                    FROM license_packages P
                    LEFT JOIN license_package_prices X ON X.license_package_id = P.id
                    LEFT JOIN durations D ON D.id = X.duration_id
                    LEFT JOIN license_package_benefits C ON C.license_package_id = P.id
                    LEFT JOIN license_benefits B ON B.id = C.license_benefit_id
                    LEFT JOIN users U ON U.id = P.created_by
                    WHERE P.status = 'Active'" ;

            // Fetching license packages with pagination
            $licensePackages = $this->paginate(25, $countSql, $queryString);

            // $licensePackages = LicensePackage::with(['prices', 'benefits'])->where('status', 'Active')->paginate($perPage);
            return $this->genericResponse(true, 'License Packages fetched successfully', 200, $licensePackages, 'getLicensePackages', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getLicensePackages", []);
        }
    }
}
