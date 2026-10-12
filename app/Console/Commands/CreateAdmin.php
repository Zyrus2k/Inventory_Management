<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'inventory:create-admin {--name=} {--email=}';

    protected $description = 'Create an administrator account (password is typed hidden, never stored in code)';

    public function handle(): int
    {
        $input = [
            'name' => $this->option('name') ?: $this->ask('Full name'),
            'email' => $this->option('email') ?: $this->ask('Email'),
            'password' => $this->secret('Password (min 8 chars, letters + numbers)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];

        $v = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $input['name'], 'email' => $input['email'],
            'password' => $input['password'], 'role' => 'admin', 'is_active' => true,
        ]);
        $this->info('Administrator created.');

        return self::SUCCESS;
    }
}
