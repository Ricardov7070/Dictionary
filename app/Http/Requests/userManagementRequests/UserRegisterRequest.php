<?php

namespace App\Http\Requests\userManagementRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegisterRequest extends FormRequest
{

    public function authorize (): bool {

        return true; 

    }


    public function rules (): array {

        return [
            'email' => 'required|string|email',
            'name' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:8',
        ];

    }

    
    public function message (): array {

        return [
            'email.required' => 'The email field is mandatory.',
            'email.email' => 'The email provided is invalid.',
            'name.required' => 'The name field is mandatory.',
            'name.string' => 'The name field must be a valid string.',
            'name.min' => 'The name field must be at least 3 characters long.',
            'name.max' => 'The name field cannot exceed 255 characters.',
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
