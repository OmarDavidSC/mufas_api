<?php 

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;

class CompanyRepository {

    public function getAllAccountsById(int $company_id, int $status = 1) : ?Collection  {
        $company = Company::find($company_id);
        return $company->cloud_accounts()->with(['cloud_service'])->wherePivot('status', $status)->where('cloud_accounts.status', $status)->get();
    }

    public function getTypeAccountBySelection(int $company_id, int $type = 1) : ?object  {
        return DB::table('companies AS COMP')
                    ->join('cloud_account_company AS CLACO', 'COMP.id', '=', 'CLACO.company_id')
                    ->join('cloud_accounts AS CLAC', 'CLACO.cloud_account_id', '=', 'CLAC.id')
                    ->join('cloud_services AS CLSER', 'CLAC.cloud_service_id', '=', 'CLSER.id')
                    ->select([
                        'CLAC.*'
                    ])
                    ->where('COMP.id', $company_id)
                    ->where('CLSER.type_id', $type)
                    ->first();
    }
}