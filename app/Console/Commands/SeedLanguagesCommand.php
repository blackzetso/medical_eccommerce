<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\TranslationKeysSeeder;
use Database\Seeders\EnglishTranslationKeysSeeder;
use ReflectionClass;

class SeedLanguagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-languages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Arabic and English languages with their translations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding languages and translations (Arabic and English)...');
        $this->newLine();

        // Check and create Arabic language
        $arabicLanguage = Language::where('code', 'ar')->first();
        
        if (!$arabicLanguage) {
            $this->info('Creating Arabic language (default)...');
            // Remove default from other languages first
            Language::where('is_default', true)->update(['is_default' => false]);
            
            $arabicLanguage = Language::create([
                'name' => 'العربية',
                'code' => 'ar',
                'status' => 'enabled',
                'is_default' => true,
            ]);
            $this->info('✓ Arabic language created successfully (set as default)!');
        } else {
            // Make sure Arabic is set as default
            if (!$arabicLanguage->is_default) {
                // Remove default from other languages
                Language::where('is_default', true)->update(['is_default' => false]);
                $arabicLanguage->update(['is_default' => true]);
                $this->info('✓ Arabic language updated to default.');
            } else {
                $this->info('✓ Arabic language already exists (default).');
            }
        }

        $this->newLine();

        // Check and create English language
        $englishLanguage = Language::where('code', 'en')->first();
        
        if (!$englishLanguage) {
            $this->info('Creating English language...');
            $englishLanguage = Language::create([
                'name' => 'English',
                'code' => 'en',
                'status' => 'enabled',
                'is_default' => false,
            ]);
            $this->info('✓ English language created successfully!');
        } else {
            $this->info('✓ English language already exists.');
        }

        $this->newLine();
        $this->info('Seeding translations...');
        $this->newLine();

        // Seed Arabic translations
        $this->info('Seeding Arabic translations...');
        $arabicSeeder = new TranslationKeysSeeder();
        $this->setSeederCommand($arabicSeeder, $this);
        $arabicSeeder->run();

        $this->newLine();

        // Seed English translations
        $this->info('Seeding English translations...');
        $englishSeeder = new EnglishTranslationKeysSeeder();
        $this->setSeederCommand($englishSeeder, $this);
        $englishSeeder->run();

        $this->newLine();
        $this->info('✅ Languages and translations seeding completed successfully!');
        
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

