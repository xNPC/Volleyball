<?php

namespace App\Actions\Fortify;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'personal_data_consent' => ['accepted', 'required'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'personal_data_consent_at' => now(),
        ]);

        $user->forceFill([
            'personal_data_consent_ip' => request()->ip(),
            'personal_data_consent_user_agent' => request()->userAgent(),
            'personal_data_consent_doc_version' => LegalDocument::findBySlug(LegalDocument::CONSENT)?->updated_at,
        ])->save();

        // Просто добавляем разрешение
        $user->permissions = ['platform.index' => true];
        $user->save();

        return $user;
    }
}
