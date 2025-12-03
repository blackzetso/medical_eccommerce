<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if brand with ID 1 already exists
        $existingBrand = Brand::find(1);
        
        if (!$existingBrand) {
            // Create brand with ID 1
            Brand::create([
                'id' => 1,
                'name' => 'عام',
                'slug' => 'general',
                'description' => 'علامة تجارية عامة',
                'status' => true
            ]);
            
            $this->command->info('Brand with ID 1 created successfully!');
        } else {
            $this->command->info('Brand with ID 1 already exists.');
        }
    }
}
