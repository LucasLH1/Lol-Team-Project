<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\LolProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'riot_pseudo' => ['required', 'string', 'max:255'],
            'riot_tag' => ['required', 'string', 'max:10'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Création du profil League of Legends
        LolProfile::create([
            'user_id' => $user->id,
            'riot_pseudo' => $input['riot_pseudo'],
            'riot_tag' => $input['riot_tag'],
        ]);

        return $user;
    }
}
