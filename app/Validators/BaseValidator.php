<?php
/* 
    Documentation
    https://github.com/rakit/validation
*/
namespace App\Validators;

use Rakit\Validation\Validator;

class BaseValidator {
    protected static function makeValidator(array $data, array $rules): array {
        $validator = new Validator;
        $validation = $validator->make($data, $rules);
        $validation->validate();

        return $validation->fails() ? $validation->errors()->all() : [];
    }
}
