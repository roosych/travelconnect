<?php

namespace App\Console\Commands;

use App\Domain\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminCreate extends Command
{
    protected $signature = 'admin:create
                            {email : Email администратора}
                            {name : Имя администратора}
                            {password : Пароль администратора}';

    protected $description = 'Создать администратора (роль operator)';

    public function handle(): int
    {
        $email    = strtolower(trim($this->argument('email')));
        $name     = trim($this->argument('name'));
        $password = (string) $this->argument('password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email'    => ['required', 'email'],
                'name'     => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Пользователь с email {$email} уже существует.");
            return self::FAILURE;
        }

        User::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => 'operator',
            'company_name'      => 'Caspirex DMC',
            'country'           => 'AZ',
            'currency_code'     => 'AZN',
            'timezone'          => 'Asia/Baku',
            'locale'            => 'ru',
            'email_verified_at' => now(),
        ]);

        $this->info("Администратор создан: {$name} <{$email}>");
        return self::SUCCESS;
    }
}
