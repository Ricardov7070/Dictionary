<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Users\User;

class userManagementController extends Controller
{
    
    protected $modelUser;

    public function __construct (User $modelUser) {

        $this->modelUser = $modelUser;

    }


    public function userAuthentication (Request $request) {

        try {

            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:8',
            ], [
                'email.required' => 'The email field is mandatory.',
                'email.email' => 'The email provided is invalid.',
                'password.required' => 'The password field is mandatory.',
                'password.min' => 'Password must be at least 8 characters long.',
            ]);
    
    
            $credentials = $request->only('email', 'password');
    
            if (Auth::attempt($credentials)) {
    
                $user = $request->user();
                $token = $user->createToken('auth_token')->plainTextToken;
            
                return response()->json([
                    "access_token" => $token,
                    "token_type" => 'Bearer'
                ]);
            
            }
    
            return response()->json([
                'message' => 'Invalid credentials!',
            ], 400);
    
        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Validation error!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 400);
    
        }    


    }


    public function registerUsers (Request $request) {

        try {

            $request->validate([
                'email' => 'required|string|email',
                "name" => 'required|string|min:3|max:255',
                'password' => 'required|string|min:8',
            ], [
                'email.required' => 'The email field is mandatory.',
                'email.email' => 'The email provided is invalid.',
                'name.required' => 'The name field is mandatory.',
                'name.string' => 'The name field must be a valid string.',
                'name.min' => 'The name field must be at least 3 characters long.',
                'name.max' => 'The name field cannot exceed 255 characters.',
                'password.required' => 'The password field is mandatory.',
                'password.min' => 'Password must be at least 8 characters long.',
            ]);
    
           $user = $this->modelUser->createUser($request);
    
            return response()->json([
                'message' => 'Sucesso!',
                'user' => $user
            ], 200);
    
        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Error in registration!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 400);
    
        }    

    }


}
