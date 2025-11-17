<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class PharmacyProductsSeeder extends Seeder
{
    public function run()
    {
        // جلب جميع الأقسام
        $categories = Category::all();
        $productsData = $this->getProductsData();

        foreach ($categories as $category) {
            // لكل قسم، أضف 4 منتجات على الأقل
            if (isset($productsData[$category->name])) {
                foreach ($productsData[$category->name] as $product) {
                    Product::create(array_merge($product, [
                        'category_id' => $category->id,
                    ]));
                }
            } else {
                // إذا لم يوجد منتجات مخصصة لهذا القسم، أضف منتجات عامة
                for ($i = 1; $i <= 4; $i++) {
                    Product::create([
                        'name' => $category->name . " منتج رقم $i",
                        'slug' => Str::slug($category->name . " منتج رقم $i") . '-' . uniqid(),
                        'description' => 'وصف المنتج التجريبي',
                        'price' => rand(50, 500),
                        'stock_quantity' => rand(10, 100),
                        'in_stock' => true,
                        'status' => true,
                        'category_id' => $category->id,
                    ]);
                }
            }
        }
    }

    private function getProductsData()
    {
        return [
            'مسكنات الألم' => [
                ['name' => 'باراسيتامول', 'slug' => 'paracetamol', 'description' => 'مسكن للألم وخافض للحرارة', 'price' => 80, 'stock_quantity' => 50, 'in_stock' => true, 'status' => true],
                ['name' => 'إيبوبروفين', 'slug' => 'ibuprofen', 'description' => 'مسكن ومضاد للالتهاب', 'price' => 90, 'stock_quantity' => 40, 'in_stock' => true, 'status' => true],
                ['name' => 'ديكلوفيناك', 'slug' => 'diclofenac', 'description' => 'مسكن ومضاد للالتهاب', 'price' => 100, 'stock_quantity' => 30, 'in_stock' => true, 'status' => true],
                ['name' => 'ترامادول', 'slug' => 'tramadol', 'description' => 'مسكن قوي للألم', 'price' => 120, 'stock_quantity' => 20, 'in_stock' => true, 'status' => true],
            ],
            'مضادات حيوية' => [
                ['name' => 'أموكسيسيلين', 'slug' => 'amoxicillin', 'description' => 'مضاد حيوي واسع المجال', 'price' => 110, 'stock_quantity' => 60, 'in_stock' => true, 'status' => true],
                ['name' => 'سيبروفلوكساسين', 'slug' => 'ciprofloxacin', 'description' => 'مضاد حيوي لعلاج الالتهابات', 'price' => 130, 'stock_quantity' => 35, 'in_stock' => true, 'status' => true],
                ['name' => 'أزيثروميسين', 'slug' => 'azithromycin', 'description' => 'مضاد حيوي لعلاج العدوى البكتيرية', 'price' => 140, 'stock_quantity' => 25, 'in_stock' => true, 'status' => true],
                ['name' => 'كلاريثروميسين', 'slug' => 'clarithromycin', 'description' => 'مضاد حيوي لعلاج الجهاز التنفسي', 'price' => 150, 'stock_quantity' => 15, 'in_stock' => true, 'status' => true],
            ],
            // ... يمكنك إضافة منتجات مخصصة لباقي الأقسام بنفس الطريقة ...
        ];
    }
}
