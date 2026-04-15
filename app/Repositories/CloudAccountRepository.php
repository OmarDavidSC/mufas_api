<?php 

namespace App\Repositories;

use App\Models\CloudAccount;

class CloudAccountRepository {
    public function getCloudAccountById(int $id) : ?CloudAccount  {
        return CloudAccount::with(['cloud_service'])->where('id', $id)->first();
    }

}