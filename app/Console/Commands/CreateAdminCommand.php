<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--username= : Unique login username}
                            {--display-name= : Optional display name}
                            {--password= : Password (min 8 chars, upper, lower, number/symbol)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->option('username') ?? $this->ask('Username');
        $displayName = $this->option('display-name');

        $isPrompting = $this->option('username') === null || $this->option('password') === null;

        if ($displayName === null && $isPrompting && $this->input->isInteractive()) {
            $displayName = $this->ask('Display name (optional, press Enter to skip)') ?: null;
        }

        $password = $this->option('password');
        if ($password === null) {
            $password = $this->secret('Password (min 8 chars, upper, lower, number/symbol)');
            $confirmation = $this->secret('Confirm password');

            if ($password !== $confirmation) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make([
            'username' => $username,
            'display_name' => $displayName,
            'password' => $password,
        ], [
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[\d\W_]/'],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, and a number or symbol.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $trimmedDisplayName = is_string($displayName) ? trim($displayName) : null;

        $admin = Admin::create([
            'username' => trim((string) $username),
            'display_name' => $trimmedDisplayName === '' ? null : $trimmedDisplayName,
            'password' => Hash::make((string) $password),
        ]);

        $this->info("Admin [{$admin->username}] created successfully.");

        return self::SUCCESS;
    }
}
