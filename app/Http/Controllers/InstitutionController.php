<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionConfig;
use App\Models\Branch;
use App\Models\InstitutionBank;
use App\Models\InstitutionContact;
use Illuminate\Http\Request;
use App\Models\MemberNoConfig;
use App\Models\Product;
use App\Models\stock;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\DB;
use App\Services\SavingAccountProductService;
use App\Services\CommissionConfigService;
use Carbon\Carbon;

class InstitutionController extends Controller
{

    protected $savingsProduct;
    protected $commissionConfig;

    public function __construct(SavingAccountProductService $savingsProduct, CommissionConfigService $commissionConfig){
        $this->savingsProduct = $savingsProduct;
        $this->commissionConfig = $commissionConfig;
    }

    public function createInstitution(Request $request){

        try{
            DB::beginTransaction();
            $userData = auth()->user();

            $instConfig = InstitutionConfig::where('type', 'institution_ref')->first();
            $instRef = $instConfig->prefix.''.$instConfig->starting.''.$instConfig->current;
            $instConfig->current= $instConfig->current + $instConfig->step;
            $instConfig->save();

            $institution=null;
            if(isset($request->id)){
                $institution = Institution::find($request->id);
                $institution->updated_by = $userData->id;
                $institution->updated_on=now();
            }else{
                $institution = new Institution();
                $institution->ref_no=$instRef;
                $institution->created_by = $userData->id;
                $institution->created_on=now();
            }
            $institution->name=$request->name;
            $institution->institution_type_id=$request->type;
            $institution->start_date=$request->start_date;
            $institution->address=$request->address;
            $institution->city_id=$request->city;
            $institution->street=$request->street;
            $institution->p_o_box=$request->p_o_box;
            $institution->is_tax_enabled = $request->tax_config;
            $institution->description=$request->description;
            $institution->status=$request->status;
            $institution->tin = $request->tin;
            $institution->save();


            if(!isset($request->id)){
                $branch = new Branch();
                $branch->name='Main';
                $branch->address=$request->address;
                $branch->city_id=$request->city;
                $branch->street=$request->street;
                $branch->code=$this->generateBranchCode();
                $branch->p_o_box=$request->p_o_box;
                $branch->institution_id=$institution->id;
                $branch->description=$request->description;
                $branch->status=$request->status;
                $branch->is_main=true;
                $branch->created_on=now();
                $branch->save();

                $bank = new InstitutionBank();
                $bank->bank_id= $request->bank_id;
                $bank->acct_name=$request->acct_name;
                $bank->acct_number=$request->acct_no;
                $bank->status=$request->status;
                $bank->branch_id=$branch->id;
                $bank->institution_id=$institution->id;
                $bank->created_on=now();
                $bank->save();

                $contact = new InstitutionContact();
                $contact->name=$request->contact_name;
                $contact->phone_number=$request->contact_number;
                $contact->email=$request->contact_email;
                $contact->website=$request->contact_web;
                $contact->status=$request->status;
                $contact->institution_id=$institution->id;
                $contact->branch_id=$branch->id;
                $contact->created_on=now();
                $contact->save();

                $productRequest=(object)[
                    "name"=>"Default Savings Product",
                    "description"=>"Default Savings Product",
                    "balance"=>0,
                    "min_balance"=>0,
                    "withdraw_allowed"=>true,
                    "overdraw_allowed"=>false,
                    "currency"=>"UGX",
                    "is_default"=>true,
                    "institution_id"=>$institution->id,
                    "branch_id"=>$branch->id,
                    "user_id"=>1,
                    "status"=>"Active"
                ];
                $product=$this->savingsProduct->createSavingsProduct($productRequest);

                $commissionRequest=(object)[
                    "name"=>$request->name,
                    "amount"=>100,
                    "commission_type"=>"PER_TRANSACTION",
                    "institution_id"=>$institution->id,
                    "branch_id"=>$branch->id,
                    "user_id"=>1,
                    "status"=>"Active",
                    "created_by"=>1,
                    "created_on"=>now()
                ];
                $commission = $this->commissionConfig->createCommissionConfig($commissionRequest);

                $memberNumberConfig = new MemberNoConfig();
                $memberNumberConfig->prefix = $this->getLetters($institution->name);
                $memberNumberConfig->institution_id_code = 1000 + $institution->id;
                $memberNumberConfig->start_from = 1000;
                $memberNumberConfig->current_value = 0;
                $memberNumberConfig->institution_id = $institution->id;
                $memberNumberConfig->created_on = now();
                $memberNumberConfig->save();
                $this->createBranchGlAccounts($institution->id,$branch->code,$branch->id);
                $this->setControlParam($institution->id);
            }
            DB::commit();
            return $this->genericResponse(true, "institution created successfully", 201, $institution);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->genericResponse(false, $th->getMessage(), 500, $th->getMessage());
        }
    }


    public function createBranch(Request $request){
        DB::beginTransaction();
        $branch = new Branch();
        $branch->name=$request->name;
        $branch->address=$request->address;
        $branch->city_id=$request->city_id;
        $branch->street=$request->street;
        $branch->code=$this->generateBranchCode();
        $branch->p_o_box=$request->p_o_box;
        $branch->institution_id=$request->institution_id;
        $branch->description=$request->description;
        $branch->status=$request->status;
        $branch->is_main=$request->is_main;
        $branch->created_on=now();
        $branch->save();

        $products = Product::where("institution_id", $request->institution_id)->get();

        foreach ($products as $value) {
            $stock = new stock();
            $stock->product_id = $value["id"];
            $stock->purchase_price = 0;
            $stock->selling_price = 0;
            $stock->min_quantity = 0;
            $stock->max_quantity = 0;
            $stock->quantity = 0;
            $stock->institution_id =$request->institution_id;
            $stock->branch_id = $branch->id;
            $stock->user_id = auth()->user()->id;
            $stock->created_by = auth()->user()->id;
            $stock->created_on = Carbon::now();
            $stock->save();
        }

        DB::commit();
        return $this->genericResponse(true, "Branch created successfully", 201, $branch);
    }


    public function getBranches(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $queryString ="SELECT B.id, B.name, B.institution_id, B.address, B.city_id, B.street, B.p_o_box,
            B.description, B.status, B.is_main, B.created_by, B.updated_by, B.created_on,
            B.updated_on, B.created_at, B.updated_at, B.code, I.name AS institution_name FROM branches B
            LEFT JOIN institutions I ON I.id = B.institution_id ";

        if ($isNotAdmin) {
            $queryString .= " WHERE B.institution_id = $userData->institution_id";
        }
        $queryString .= " ORDER BY B.id DESC ";
        $branches = DB::select($queryString);
        return $this->genericResponse(true, "", 200, $branches);
    }

    public function getInstitutions()
    {
        try {
            // $institutions = Institution::orderBy('id', 'desc')->get();
            $institutions = DB::table('institutions as I')
                ->select(
                    'I.id','I.name','I.ref_no','I.institution_type_id','I.start_date','I.address','I.city_id','I.street','I.p_o_box',
                    'I.description','I.status','I.created_by','I.updated_by','I.created_on','I.updated_on','I.created_at','I.updated_at',
                    'I.is_tax_enabled','I.tin','T.name as type_name','C.name as city_name'
                )
                ->join('institution_type_refs as T', 'I.institution_type_id', '=', 'T.id')
                ->leftJoin('city_refs as C', 'C.id', '=', 'I.city_id')
                ->leftJoin('users as U', 'U.id', '=', 'I.created_by')
                ->where('I.status', 'Active')
                ->orderBy("I.id","DESC")
                ->get();
            return $this->genericResponse(true, "institution created successfully", 201, $institutions);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th);
        }
    }


    public function getInstitutionProfile(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $queryString ="SELECT I.id, I.name, I.ref_no, I.institution_type_id, I.start_date, I.address,
        I.city_id, I.street, I.p_o_box, I.description, I.status, I.created_by, I.updated_by,
        I.created_on, I.updated_on, I.created_at, I.updated_at, I.is_tax_enabled, I.tin,
        T.name AS type_name, C.name AS city_name, CONCAT(U.first_name,' ', U.last_name,' ',U.other_name),
        K.name AS contact_name, K.phone_number AS contact_phone_number, K.email AS contact_email,
        K.website, B.bank_id, B.acct_name, B.acct_number, A.name AS bank_name FROM institutions I
        INNER JOIN institution_type_refs T ON I.institution_type_id = T.id
        LEFT JOIN city_refs C ON C.id = I.city_id
        LEFT JOIN users U ON U.id = I.created_by
        LEFT JOIN institution_contacts K ON K.institution_id = I.id
        LEFT JOIN institution_banks B ON B.institution_id = I.id
        LEFT JOIN bank_refs A ON A.id = B.bank_id ";

        $queryString .= " WHERE I.status=  '$request->status'  AND I.id = $request->institutionId";
        $institutionDetails = DB::select($queryString);
        $branches = Branch::where("institution_id", $request->institutionId)->get();

        $institutionDetails[0]->branches = $branches;
        $institutionDetails[0]->users = $this->getUsersByInstitution($request->institutionId);
        return $this->genericResponse(true, "institution created successfully", 201, $institutionDetails);
    }

    public function getBranchesByInstitutionId(Request $request){
        $branches = Branch::where(['status'=>$request->status, 'institution_id'=>$request->institutionId])->get();
        return $this->genericResponse(true, "Fetched institution branches", 200, $branches);
    }


    public function generateMissingCodesForBranches(){
        $branches = Branch::all();
        foreach ($branches as $value) {
            $branch=Branch::find($value["id"]);
            if(empty($branch->code)){
                $branch->code = $this->generateBranchCode();
                $branch->save();
            }
        }
        return $this->genericResponse(true, "Branch codes created successfully", 200, $branches);
    }

    public function generateMissingLedgers(){
        $branches = Branch::all();
        $myArray = array();
        foreach ($branches as $value) {
            $branch=Branch::find($value["id"]);
            $results=$this->createBranchGlAccounts($value['institution_id'],$value['code'],$value['id']);
            array_push($myArray, $results);
        }
        return $this->genericResponse(true, "Branch codes created successfully", 200, $myArray);
    }

    public function generateMissingCntrlParameter(){
        $myArray = array();
        $institutions = Institution::all();
        foreach ($institutions as $key => $value) {
            $results=$this->setControlParam($value['id']);
            array_push($myArray, $results);
        }
        return $this->genericResponse(true, "Control parameter set successfully", 200, $myArray);
    }

    public function getInstitutionDetails(){
        $userData = auth()->user();
        $institution = Institution::find($userData->institution_id);
        return $this->genericResponse(true, "Institution details", 200, $institution);
    }
}
