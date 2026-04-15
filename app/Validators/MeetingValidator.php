<?php

namespace App\Validators;

class MeetingValidator extends BaseValidator {
    public static function store(array $data, array $files = []): array {
        $rules = [
            'topic' 		 		=> 'required',
			'company_id'			=> 'required|numeric'
        ];
        return self::makeValidator(array_merge($data, $files), $rules);
    }
}
