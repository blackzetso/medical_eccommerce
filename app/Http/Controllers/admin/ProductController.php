<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Build hierarchical categories list for dropdowns
     */
    private function buildHierarchicalCategories($categories = null, $parent_id = null, $prefix = '')
    {
        if ($categories === null) {
            $categories = Category::where('status', true)->get();
        }

        $result = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parent_id) {
                $result[] = [
                    'id' => $category->id,
                    'name' => $prefix . $category->name,
                    'level' => strlen($prefix) / 2
                ];

                // Get children recursively
                $children = $this->buildHierarchicalCategories($categories, $category->id, $prefix . '-- ');
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10); 

        // Debug: Log the products data and trigger accessor
        Log::info('=== DEBUG PRODUCTS IN CONTROLLER ===');
        foreach ($products as $product) {
            Log::info('Product ID: ' . $product->id);
            Log::info('Product name: ' . $product->name);
            Log::info('Product images raw: ' . ($product->attributes['images'] ?? 'NULL'));
            Log::info('Product images casted: ' . json_encode($product->images));
            Log::info('Product images type: ' . gettype($product->images));
        }

        $categories = Category::where('status', true)->get();
        $brands = Brand::where('status', true)->get();

        // Debug: Log data being sent to Inertia
        Log::info('Data being sent to Inertia:', [
            'products_count' => $products->count(),
            'first_product' => $products->first() ? [
                'id' => $products->first()->id,
                'name' => $products->first()->name,
                'images' => $products->first()->images
            ] : null
        ]);

        return Inertia::render('Admin/theme1/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hierarchicalCategories = $this->buildHierarchicalCategories();
        $brands = Brand::where('status', true)->get();
        $attributes = Attribute::with('values')->where('status', true)->get();

        return Inertia::render('Admin/theme1/Products/Create', [
            'categories' => $hierarchicalCategories,
            'brands' => $brands,
            'attributes' => $attributes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'sku' => 'nullable|string|max:255|unique:products',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
            'attributes.*.price_adjustment' => 'nullable|numeric'
        ]);

        // معالجة الصور
        $images = [];
        if ($request->hasFile('images')) {
            Log::info('Images found in request');
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = '/storage/' . $path;
                Log::info('Stored image at: ' . $path);
            }
        } else {
            Log::info('No images found in request');
        }
        $validated['images'] = $images;

        // حساب sale_price بناءً على نوع الخصم
        if ($validated['discount_type'] !== 'none' && $validated['discount_value'] > 0) {
            $price = $validated['price'];
            $discountValue = $validated['discount_value'];

            if ($validated['discount_type'] === 'fixed') {
                $validated['sale_price'] = max(0, $price - $discountValue);
            } elseif ($validated['discount_type'] === 'percentage') {
                $discountAmount = ($price * $discountValue) / 100;
                $validated['sale_price'] = max(0, $price - $discountAmount);
            }
        } else {
            $validated['sale_price'] = null;
        }

        Log::info('Final images array:', $images);
        $product = Product::create($validated);

        // حفظ الخصائص إذا تم تمريرها
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attribute) {
                $product->attributes()->attach($attribute['attribute_id'], [
                    'attribute_value_id' => $attribute['attribute_value_id'],
                    'price_adjustment' => $attribute['price_adjustment'] ?? 0
                ]);
            }
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'تم إنشاء المنتج بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with(['category', 'brand', 'attributes', 'attributeValues'])->findOrFail($id);
        $hierarchicalCategories = $this->buildHierarchicalCategories();
        $brands = Brand::where('status', true)->get();
        $attributes = Attribute::with('values')->where('status', true)->get();

        // تحضير الخصائص الحالية للمنتج
        $productAttributes = [];
        foreach ($product->attributes as $attribute) {
            $productAttributes[] = [
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $attribute->pivot->attribute_value_id,
                'price_adjustment' => $attribute->pivot->price_adjustment
            ];
        }

        return Inertia::render('Admin/theme1/Products/Edit', [
            'product' => $product,
            'categories' => $hierarchicalCategories,
            'brands' => $brands,
            'attributes' => $attributes,
            'productAttributes' => $productAttributes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'existing_images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
            'attributes.*.price_adjustment' => 'nullable|numeric'
        ]);

        // معالجة الصور الجديدة
        $existingImages = $validated['existing_images'] ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $existingImages[] = '/storage/' . $path;
            }
        }
        $validated['images'] = $existingImages;

        // حساب sale_price بناءً على نوع الخصم
        if ($validated['discount_type'] !== 'none' && $validated['discount_value'] > 0) {
            $price = $validated['price'];
            $discountValue = $validated['discount_value'];

            if ($validated['discount_type'] === 'fixed') {
                $validated['sale_price'] = max(0, $price - $discountValue);
            } elseif ($validated['discount_type'] === 'percentage') {
                $discountAmount = ($price * $discountValue) / 100;
                $validated['sale_price'] = max(0, $price - $discountAmount);
            }
        } else {
            $validated['sale_price'] = null;
        }

        $product->update($validated);

        // تحديث الخصائص
        $product->attributes()->detach(); // حذف الخصائص القديمة

        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attribute) {
                $product->attributes()->attach($attribute['attribute_id'], [
                    'attribute_value_id' => $attribute['attribute_value_id'],
                    'price_adjustment' => $attribute['price_adjustment'] ?? 0
                ]);
            }
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // حذف الصور من storage قبل حذف المنتج
        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $imagePath) {
                // إزالة /storage/ من المسار للحصول على المسار الحقيقي
                $relativePath = str_replace('/storage/', '', $imagePath);
                $fullPath = storage_path('app/public/' . $relativePath);

                // التحقق من وجود الملف وحذفه
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    Log::info('Deleted image: ' . $fullPath);
                } else {
                    Log::warning('Image not found for deletion: ' . $fullPath);
                }
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                        ->with('success', 'تم حذف المنتج والصور بنجاح');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => !$product->status]);

        return redirect()->back()->with('success', 'تم تعديل حالة المنتج');
    }
}
