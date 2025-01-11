<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'deleted_at'
    ];


    public function createUser ($request): array {

        $user = self::create([
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'password' => Hash::make($request->input('password')),
        ]);
    
        return [
            'id_user' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('d/m/Y'),
        ];

    }


    public function userValidation ($request): array {

        return self::where('name', $request->input('name'))
                    ->where('email', $request->input('email'))
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();

    }


    public function userEmailValidation ($request): array {

        return self::where('email', $request->input('email'))
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();

    }


    public function viewUsers (): array {

        return self::whereNull('deleted_at')
                    ->select('id', 'name', 'email', 'created_at', 'updated_at')
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'created_at' => $user->created_at->format('d/m/Y'),
                            'updated_at' => $user->updated_at->format('d/m/Y'),
                        ];
                    })
                    ->toArray();

    }


    public function randomUserPassword ($existingUser): string {

        $temporaryPassword = Str::random(8);

        $user = self::findOrFail($existingUser[0]['id']);

        $user->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        return $temporaryPassword;

    }


    public function updateUser ($request, $id_user): array {

        $user = self::findOrFail($id_user);

        $user->update([
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'password' => Hash::make($request->input('password')),
        ]);

        return [
            'id_user' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at->format('d/m/Y'),
        ];

    }


    public function searchUser ($id_user): ?User {

        return self::where('id', $id_user)
                    ->whereNull('deleted_at')
                    ->first(); 

    }


    public function deleteUser ($id_user): array {

        $user = self::findOrFail($id_user);

        $user->update([
            'deleted_at' => now(),
        ]);

        return [
            'id_user' => $user->id,
            'name' => $user->name,
        ];

    }

}
