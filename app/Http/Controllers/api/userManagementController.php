<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Users\User;
use App\Http\Requests\userManagementRequests\UserLoginRequest;
use App\Http\Requests\userManagementRequests\UserRegisterRequest;
use App\Http\Requests\userManagementRequests\UserUpdateRequest;
use App\Http\Requests\userManagementRequests\forgotPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Services\EmailService;

class userManagementController extends Controller
{
    
    protected $modelUsers;
    protected $emailService;


    public function __construct (User $modelUsers, EmailService $emailService) {

        $this->modelUsers = $modelUsers;
        $this->emailService = $emailService;

    }


    public function index (): JsonResponse {

        return response()->json([
            'message' => "Fullstack Challenge 🏅 - Dictionary"
        ], 200);

    }


    public function userAuthentication (UserLoginRequest $request): JsonResponse {

        try {
    
            $credentials = $request->only('email', 'password');

            $user = $this->modelUsers::where('email', $credentials['email'])->whereNull('deleted_at')->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {

                return response()->json([
                    'message' => 'Invalid credentials!',
                ], 400);

            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => 'Login successful!',
                'id_user' => $user->id,
                'user' => $user->name,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
    
        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Validation error!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
            
        }    

    }


    public function registerUsers (UserRegisterRequest $request): JsonResponse {

        try {

            $record = $this->validatorUsersRegistered($request);

            if ($record->getData() !== []) {
                return $record;
            }
    
            $user = $this->modelUsers->createUser($request);

            return response()->json([
                'success' => 'Successfully registered!',
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
            ], 500);            
    
        }    

    }


    public function validatorUsersRegistered ($request): JsonResponse {

        $userValidation = $this->modelUsers->userValidation($request);

        if (!empty($userValidation)) {

            return response()->json([
                'message' => 'User already registered!',
            ], 400);
        
        }

        $userEmailValidation = $this->modelUsers->userEmailValidation($request);

        if (!empty($userEmailValidation)) {

            return response()->json([
                'message' => 'Email already registered with another user!',
            ], 400);
        
        }

        return response()->json([], 200);

    }


    public function forgotPassword (forgotPasswordRequest $request): JsonResponse {

        try {

            $existingUser = $this->modelUsers->userValidation($request);

            if (!$existingUser) {

                return response()->json([
                    'message' => 'Invalid "Name" or "Email"!',
                ], 400);

            } else {

                $content = $this->modelUsers->randomUserPassword($existingUser);

                $this->emailService->sendEmail(
                                              $request->input('email'), 
                                         'Authentication Password!', 
                                            'Your generated random password is: ' . $content, 
                                            null
                                              );

                return response()->json([
                    'success' => 'Email sent successfully!',
                ], 200);

            }

        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Error sending email!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function viewAuthenticatedProfile (): JsonResponse {

        try {

            $existingUser = $this->modelUsers->searchUser(auth()->id());

            return response()->json([
                'status' => 'Authenticated!',
                'id' => $existingUser->id,
                'name' => $existingUser->name,
                'email' => $existingUser->email,
                'created_at' => $existingUser->created_at->format('d/m/Y'),
                'updated_at' => $existingUser->updated_at->format('d/m/Y')
            ]);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function viewRecord (): JsonResponse {

        try {

            $user = $this->modelUsers->viewUsers();

            return response()->json([
                'users' => $user
            ]);     
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function updateRecord (UserUpdateRequest $request, $id_user): JsonResponse {

        try {

            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $existingUser = $this->modelUsers->searchUser($id_user);

            if (!$existingUser) {

                return response()->json([
                    'message' => 'Undefined User!',
                ], 400);

            } else {

                $record = $this->usersUpdateValidator($request, $id_user);

                if ($record->getData() !== []) {
                    return $record;
                }


                $user = $this->modelUsers->updateUser($request, $id_user);

                $existingUser->tokens()->delete();

                return response()->json([
                    'success' => 'Updated successfully!',
                    'user' => $user
                ], 200); 

            }      
    
        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Error when updating!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }    

    }


    public function usersUpdateValidator ($request, $id_user): JsonResponse {

        $userValidation = $this->modelUsers->userValidation($request);

        if (!empty($userValidation)) {

            if ($id_user != $userValidation[0]['id']) {

                return response()->json([
                    'message' => 'There is already a user with these credentials registered!',
                ], 400);

            }
            
        }


        $userEmailValidation = $this->modelUsers->userEmailValidation($request);

        if (!empty($userEmailValidation)) {

            if ($id_user != $userEmailValidation[0]['id']) {

                return response()->json([
                    'message' => 'There is already a user with this email registered!',
                ], 400);
            
            }
    
        }

        return response()->json([], 200);

    }


    public function logoutUser ($id_user): JsonResponse {

        try {

            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $existingUser = $this->modelUsers->searchUser($id_user);

            if (!$existingUser) {

                return response()->json([
                    'message' => 'Undefined User!',
                ], 400);

            } else {

                $existingUser->tokens()->delete();

                return response()->json([
                    'success' => 'Logout completed successfully!',
                ], 200); 

            }      
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }    

    }


    public function deleteRecord ($id_user): JsonResponse {

        try {

            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $existingUser = $this->modelUsers->searchUser($id_user);

            if (!$existingUser) {

                return response()->json([
                    'message' => 'Undefined User!',
                ], 400);

            } else {

                $existingUser->tokens()->delete();

                $user = $this->modelUsers->deleteUser($id_user);

                return response()->json([
                    'success' => 'Successfully deleted!',
                    'user' => $user,
                    'status' => 'Deletado.'
                ], 200); 

            }      
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }    

    }

}
