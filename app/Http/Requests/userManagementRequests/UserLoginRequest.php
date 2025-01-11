<?php

namespace App\Http\Requests\userManagementRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLoginRequest extends FormRequest
{

    public function authorize (): bool {

        return true; 

    }

    
    public function rules (): array {

        return [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ];

    }

    
    public function messages (): array {

        return [
            'email.required' => 'The email field is mandatory.',
            'email.email' => 'The email provided is invalid.',
            'password.required' => 'The password field is mandatory.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];

    }


    protected function failedValidation (Validator $validator): never {

        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ],400));

    }  

}

