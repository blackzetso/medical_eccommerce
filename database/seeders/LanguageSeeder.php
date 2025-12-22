<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء اللغة العربية كلغة افتراضية
        Language::firstOrCreate(
            ['code' => 'ar'],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'status' => 'enabled',
                'is_default' => true,
            ]
        );

        $this->command->info('Default language (Arabic) created successfully.');
    }
}
