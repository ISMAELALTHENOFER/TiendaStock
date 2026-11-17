<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateFirstAdmin extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create an administrator account without a default password';

    public function handle(): int
    {
        if (User::where('role', User::ROLE_ADMIN)->where('activo', true)->exists()) {
            $this->error('An active administrator already exists. Use the admin user-management screen instead.');

            return self::FAILURE;
        }

        do {
            $data = [
                'name' => $this->ask('Name'),
                'username' => $this->ask('Username'),
                'email' => $this->ask('Email'),
                'password' => $this->secret('Password'),
                'password_confirmation' => $this->secret('Confirm password'),
            ];
            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }
            }
        } while ($validator->fails());

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ADMIN,
            'activo' => true,
        ]);

        $this->info('Administrator created. Store the credentials securely.');

        return self::SUCCESS;
    }
}
