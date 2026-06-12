<?php

namespace App\Console\Commands;

use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Illuminate\Console\Command;

class AppInstall extends Command
{
    protected $signature   = 'app:install';
    protected $description = 'Засеять справочники, нужные для работы приложения (валюты, страны)';

    /**
     * Справочные сидеры. Дополнять список по мере появления новых
     * (напр. ServiceTypeSeeder::class). Идемпотентны — гонять можно повторно.
     *
     * @var array<int, class-string<\Illuminate\Database\Seeder>>
     */
    protected array $seeders = [
        CurrencySeeder::class,
        CountrySeeder::class,
    ];

    public function handle(): int
    {
        foreach ($this->seeders as $seeder) {
            $this->components->task($seeder, function () use ($seeder) {
                $this->callSilent('db:seed', ['--class' => $seeder, '--force' => true]);
            });
        }

        $this->newLine();
        $this->info('Справочники готовы. Администраторы создаются командой admin:create.');

        return self::SUCCESS;
    }
}
