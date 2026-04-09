<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $registerRequest = app(RegisterRequest::class);
        Validator::make($input, $registerRequest->rules(), $registerRequest->messages())->validate();

        $existingUser = User::where('email', $input['email'])->first();

        if ($existingUser && $existingUser->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [$registerRequest->messages()['email.unique']],
            ]);
        }

        if ($existingUser) {
            $existingUser->forceFill([
                'name' => $input['name'],
                'password' => Hash::make($input['password']),
            ])->save();

            $existingUser->sendEmailVerificationNotification();

            return $existingUser;
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }
}
