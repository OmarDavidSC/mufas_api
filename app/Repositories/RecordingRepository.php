<?php 

namespace App\Repositories;

use App\Models\CloudAccount;

class AccountRepository {

    public function getAccountById(int $account_id): ?CloudAccount {
        return CloudAccount::whereId($account_id)->first();
    }

}