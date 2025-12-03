<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class FooterSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Footer contact information settings
        Setting::updateOrCreate(
            ['key' => 'footer_address'],
            ['value' => '']
        );

        Setting::updateOrCreate(
            ['key' => 'footer_phone'],
            ['value' => '']
        );

        Setting::updateOrCreate(
            ['key' => 'footer_email'],
            ['value' => '']
        );

        Setting::updateOrCreate(
            ['key' => 'footer_working_hours'],
            ['value' => '']
        );

        // Footer social media links
        Setting::updateOrCreate(
            ['key' => 'footer_facebook_url'],
            ['value' => '#']
        );

        Setting::updateOrCreate(
            ['key' => 'footer_twitter_url'],
            ['value' => '#']
        );

        Setting::updateOrCreate(
            ['key' => 'footer_instagram_url'],
            ['value' => '#']
        );
    }
}

