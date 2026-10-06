<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdmin extends Command
{
    protected $signature = 'bansawali:admin {--reset-password : Replace the existing password with ADMIN_PASSWORD}';

    protected $description = 'Create the configured administrator securely';

    public function handle(): int
    {
        $email = config('bansawali.admin_email');
        $password = config('bansawali.admin_password');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            $this->error('Set a valid ADMIN_EMAIL and ADMIN_PASSWORD of at least 12 characters in .env.');

            return self::FAILURE;
        }
        $user = User::firstOrNew(['email' => $email]);
        if ($user->exists && ! $this->option('reset-password')) {
            $this->info('Account exists; no credentials or permissions changed.');

            return self::SUCCESS;
        }
        $user->name = config('bansawali.admin_name');
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator ready: '.$email);

        return self::SUCCESS;
    }
}
