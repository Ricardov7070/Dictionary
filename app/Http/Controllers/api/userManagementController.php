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


/**
 * @OA\Info(
 *     title="API do Dicionário",
 *     version="1.0.0",
 *     description="Documentação da API do Dicionário. Fullstack Challenge 🏅 - Dictionary",
 *     @OA\Contact(
 *         email="suporte@dicionario.com"
 *     )
 * )
 */

class userManagementController extends Controller
{
    
    protected $modelUsers;
    protected $emailService;


// Método Construtor
    public function __construct (User $modelUsers, EmailService $emailService) {

        $this->modelUsers = $modelUsers;
        $this->emailService = $emailService;

    }


/**
 * @OA\Get(
 *     path="/api/",
 *     summary="Bem-Vindo ao Dicionário!",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="No registration!"
 *     )
 * )
 */
    public function index (): JsonResponse {

        return response()->json([
            'message' => "Fullstack Challenge 🏅 - Dictionary"
        ], 200);

    }


/**
 * @OA\Post(
 *     path="/api/auth/signin",
 *     summary="Realiza a autenticação do usuário.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Login successful!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid credentials!, Validation error!"
 *     ),
 * )
 */
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


/**
 * @OA\Post(
 *     path="/api/auth/signup",
 *     summary="Realiza o registro do usuário.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully registered!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error in registration!"
 *     ),
 * )
 */
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

// Realiza a validação da existência de usuários já salvos no banco de dados.
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


/**
 * @OA\Post(
 *     path="/api/auth/forgotPassword",
 *     summary="Realiza o envio de uma senha aleatória via email para o usuário que esqueceu sua chave de acesso.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Email sent successfully!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error sending email!, Invalid Name or Email"
 *     ),
 * )
 */
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


/**
 * @OA\Get(
 *     path="/api/user/me/",
 *     summary="Realiza a visualização do usuário autenticado.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 * )
 */
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


/**
 * @OA\Get(
 *     path="/api/viewRecord",
 *     summary="Realiza a visualização de todos os usuário registrados.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 * )
 */
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


/**
 * @OA\Put(
 *     path="/api/updateRecord/{id_user}",
 *     summary="Realiza a atualização de dados cadastrais do usuário registrado.",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Updated successfully!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Undefined User!, Error when updating!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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


// Realiza a validação de dados permitidos para o usuário que está atualizando suas informações no banco de dados
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


/**
 * @OA\Post(
 *     path="/api/logoutUser/{id_user}",
 *     summary="Realiza o logout do usuário atual autenticado",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Logout completed successfully!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Undefined User!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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


/**
 * @OA\Delete(
 *     path="/api/deleteRecord/{id_user}",
 *     summary="Realiza a exclusão do usuário selecionado do banco de dados",
 *     tags={"Gerenciamento de Usuário"},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully deleted!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Undefined User!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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
