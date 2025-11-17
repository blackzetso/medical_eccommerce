<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\LanguagePhrase;

class SliderTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all languages
        $languages = Language::all();

        // Translations for sliders module
        $translations = [
            'sliders' => [
                'ar' => 'السلايدرز',
                'en' => 'Sliders',
            ],
            'slider' => [
                'ar' => 'سلايدر',
                'en' => 'Slider',
            ],
            'add_slider' => [
                'ar' => 'إضافة سلايدر',
                'en' => 'Add Slider',
            ],
            'edit_slider' => [
                'ar' => 'تعديل سلايدر',
                'en' => 'Edit Slider',
            ],
            'slider_title' => [
                'ar' => 'عنوان السلايدر',
                'en' => 'Slider Title',
            ],
            'slider_description' => [
                'ar' => 'وصف السلايدر',
                'en' => 'Slider Description',
            ],
            'slider_image' => [
                'ar' => 'صورة السلايدر',
                'en' => 'Slider Image',
            ],
            'slider_link' => [
                'ar' => 'رابط السلايدر',
                'en' => 'Slider Link',
            ],
            'button_text' => [
                'ar' => 'نص الزر',
                'en' => 'Button Text',
            ],
            'sort_order' => [
                'ar' => 'ترتيب العرض',
                'en' => 'Sort Order',
            ],
        ];

        foreach ($languages as $language) {
            foreach ($translations as $key => $values) {
                $word = $values[$language->code] ?? $values['en'];

                LanguagePhrase::updateOrCreate(
                    [
                        'language_id' => $language->id,
                        'key' => $key,
                        'group' => 'general'
                    ],
                    [
                        'word' => $word
                    ]
                );
            }
        }

        $this->command->info('✅ Slider translations seeded successfully!');
    }
}
