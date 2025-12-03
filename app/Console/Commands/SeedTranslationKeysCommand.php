<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TranslationKeysSeeder;
use ReflectionClass;

class SeedTranslationKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-translation-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Arabic translation keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding Arabic translation keys...');
        $this->newLine();

        $seeder = new TranslationKeysSeeder();
        $this->setSeederCommand($seeder, $this);
        $seeder->run();

        $this->newLine();
        $this->info('✅ Translation keys seeding completed successfully!');
        
        return 0;
    }

    /**
     * Set the command property on a seeder using reflection
     */
    protected function setSeederCommand($seeder, $command)
    {
        $reflection = new ReflectionClass($seeder);
        $property = $reflection->getProperty('command');
        $property->setAccessible(true);
        $property->setValue($seeder, $command);
    }
}

