<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Convert full URL to relative path
     * Converts http://domain.com/uploads/products/image.jpg to /uploads/products/image.jpg
     */
    private function convertUrlToRelativePath($url)
    {
        if (empty($url)) {
            return null;
        }

        // If it's already a relative path, return as is
        if (strpos($url, '/uploads/') === 0) {
            return $url;
        }

        // Extract path from full URL
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['path'])) {
            $path = $parsedUrl['path'];
            // Ensure it starts with /uploads/products/
            if (strpos($path, '/uploads/products/') === 0) {
                return $path;
            }
        }

        // If we can't parse it, try to extract /uploads/products/ from the string
        if (preg_match('#(/uploads/products/[^?#]+)#', $url, $matches)) {
            return $matches[1];
        }

        // If all else fails, return null
        Log::warning('Could not convert URL to relative path', ['url' => $url]);
        return null;
    }

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
            $searchTerm = trim($request->search);
            
            // Check if search term matches product_code exactly
            $hasExactProductCode = Product::whereNotNull('product_code')
                ->where('product_code', '=', $searchTerm)
                ->exists();
            
            // Check if search term matches sku exactly
            $hasExactSku = Product::whereNotNull('sku')
                ->where('sku', '=', $searchTerm)
                ->exists();
            
            if ($hasExactProductCode) {
                // Exact match for product_code only
                $query->where('product_code', '=', $searchTerm);
            } elseif ($hasExactSku) {
                // Exact match for sku only
                $query->where('sku', '=', $searchTerm);
            } else {
                // Partial match for name
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        // تعديل الصور لإرسال رابط كامل
        $products->getCollection()->transform(function ($product) {
            // تعديل main_image إذا كان موجوداً
            if ($product->main_image) {
                $product->main_image = asset('/' . ltrim($product->main_image, '/'));
            }
            // تعديل صور المصفوفة
            if (is_array($product->images)) {
                $product->images = array_map(fn($img) => asset('/' . ltrim($img, '/')), $product->images);
            }
            return $product;
        });

        $categories = Category::where('status', true)->get();
        $brands = Brand::where('status', true)->get();

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
            'name' => $request->input('name') ? 'required|string|max:255' : 'nullable',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => $request->input('price') ? 'required|numeric|min:0' : 'nullable',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => $request->filled('discount_type') ? 'required|in:none,fixed,percentage' : 'nullable',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'sku' => 'nullable|string|max:255|unique:products',
            'product_code' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array', // Images are now optional since we have main_image
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
            'attributes.*.price_adjustment' => 'nullable|numeric',
            'colors' => 'nullable|array', // Validate colors as an optional array
        ]);

        // معالجة الصورة الرئيسية ورفعها إلى public/uploads/products
        if ($request->hasFile('main_image')) {
            $mainImage = $request->file('main_image');
            $mainImageFilename = uniqid() . '_main_' . time() . '.' . $mainImage->getClientOriginalExtension();
            $mainImage->move(public_path('uploads/products'), $mainImageFilename);
            $validated['main_image'] = '/uploads/products/' . $mainImageFilename;
        }

        // معالجة الصور الإضافية ورفعها إلى public/uploads/products
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $filename);
                $images[] = '/uploads/products/' . $filename;
            }
        }
        $validated['images'] = $images;

        // حساب sale_price بناءً على نوع الخصم
        if (isset($validated['discount_type']) && $validated['discount_type'] !== 'none' && isset($validated['discount_value']) && $validated['discount_value'] > 0) {
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

        $product = Product::create($validated);

        // حفظ الألوان إذا تم تمريرها
        if ($request->has('colors')) {
            $product->colors = $validated['colors'];
            $product->save();
        }

        // حفظ الخصائص إذا تم تمريرها
        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attribute) {
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
        // تعديل الصور لإرسال رابط كامل
        if (is_array($product->images)) {
            $product->images = array_map(fn($img) => asset('/' . ltrim($img, '/')), $product->images);
        }
        $hierarchicalCategories = $this->buildHierarchicalCategories();
        $brands = Brand::where('status', true)->get();
        $attributes = Attribute::with('values')->where('status', true)->get();

        // تحضير الخصائص الحالية للمنتج
        $productAttributes = [];
        foreach ($product->attributes()->get() as $attribute) {
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

        // تحويل القيم قبل validation
        $requestData = $request->all();
        
        // تحويل boolean strings إلى booleans
        $booleanFields = ['manage_stock', 'is_featured', 'status'];
        foreach ($booleanFields as $field) {
            if (isset($requestData[$field])) {
                $value = $requestData[$field];
                if (is_string($value)) {
                    $requestData[$field] = in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
                }
            }
        }
        
        // تحويل JSON strings إلى arrays
        if (isset($requestData['attributes']) && is_string($requestData['attributes'])) {
            $decoded = json_decode($requestData['attributes'], true);
            if (is_array($decoded)) {
                $requestData['attributes'] = $decoded;
            } else {
                $requestData['attributes'] = [];
            }
        }
        
        if (isset($requestData['colors']) && is_string($requestData['colors'])) {
            $decoded = json_decode($requestData['colors'], true);
            if (is_array($decoded)) {
                $requestData['colors'] = $decoded;
            } else {
                $requestData['colors'] = [];
            }
        }
        
        // استبدال request data
        $request->merge($requestData);
        
        // Log initial request data
        Log::info('=== Product Update Request Started ===', [
            'product_id' => $id,
            'has_main_image_file' => $request->hasFile('main_image'),
            'has_images_files' => $request->hasFile('images'),
            'existing_images_input' => $request->input('existing_images'),
            'existing_images_type' => gettype($request->input('existing_images')),
            'all_request_input_keys' => array_keys($request->all()),
            'manage_stock_value' => $request->input('manage_stock'),
            'manage_stock_type' => gettype($request->input('manage_stock')),
            'is_featured_value' => $request->input('is_featured'),
            'is_featured_type' => gettype($request->input('is_featured')),
            'status_value' => $request->input('status'),
            'status_type' => gettype($request->input('status')),
            'attributes_value' => $request->input('attributes'),
            'attributes_type' => gettype($request->input('attributes')),
            'colors_value' => $request->input('colors'),
            'colors_type' => gettype($request->input('colors')),
            'current_product_images' => $product->images,
        ]);

        $validated = $request->validate([
            'name' => $request->input('name') ? 'required|string|max:255' : 'nullable',
            'name_en' => 'nullable|string|max:255',
            'slug' => $request->input('slug') ? 'required|string|max:255|unique:products,slug,' . $id : 'nullable',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => $request->input('price') ? 'required|numeric|min:0' : 'nullable',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => $request->filled('discount_type') ? 'required|in:none,fixed,percentage' : 'nullable',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => $request->input('stock_quantity') ? 'required|integer|min:0' : 'nullable',
            'manage_stock' => 'boolean',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'product_code' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_featured' => 'boolean',
            'status' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'existing_main_image' => 'nullable|string',
            'existing_images' => 'array',
            'images' => $request->hasFile('images') ? 'required|array|min:1' : 'nullable|array', // Accept existing images if no new images are uploaded
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
            'attributes.*.price_adjustment' => 'nullable|numeric',
            'colors' => 'nullable|array', // Validate colors as an optional array
        ]);

        // معالجة الصورة الرئيسية ورفعها إلى public/uploads/products
        if ($request->hasFile('main_image')) {
            // حذف الصورة الرئيسية القديمة إذا كانت موجودة
            if ($product->main_image && file_exists(public_path($product->main_image))) {
                unlink(public_path($product->main_image));
            }
            
            $mainImage = $request->file('main_image');
            $mainImageFilename = uniqid() . '_main_' . time() . '.' . $mainImage->getClientOriginalExtension();
            $mainImage->move(public_path('uploads/products'), $mainImageFilename);
            $validated['main_image'] = '/uploads/products/' . $mainImageFilename;
        } else {
            // الاحتفاظ بالصورة الرئيسية الحالية إذا لم يتم رفع صورة جديدة
            $validated['main_image'] = $validated['existing_main_image'] ?? $product->main_image;
        }

        // معالجة الصور الإضافية - الحصول على الصور الحالية المتبقية
        $existingImages = [];
        
        // التحقق من وجود existing_images (حتى لو كانت فارغة)
        // يمكن أن تكون array أو JSON string
        $hasExistingImages = $request->has('existing_images') || 
                           $request->input('existing_images') !== null;
        
        Log::info('Processing existing images', [
            'has_existing_images' => $hasExistingImages,
            'request_has' => $request->has('existing_images'),
            'existing_images_input_raw' => $request->input('existing_images'),
            'existing_images_input_type' => gettype($request->input('existing_images')),
        ]);
        
        // إذا تم إرسال existing_images (حتى لو كانت فارغة)، استخدمها
        // هذا مهم: إذا أرسل المستخدم array فارغ، يعني أنه حذف جميع الصور القديمة
        if ($hasExistingImages) {
            $existingImagesInput = $request->input('existing_images');
            
            Log::info('Existing images input received', [
                'type' => gettype($existingImagesInput),
                'value' => $existingImagesInput,
            ]);
            
            if (is_array($existingImagesInput)) {
                $existingImages = $existingImagesInput;
            } elseif (is_string($existingImagesInput)) {
                // محاولة تحويل JSON string إلى array
                $decoded = json_decode($existingImagesInput, true);
                if (is_array($decoded)) {
                    $existingImages = $decoded;
                } else {
                    // إذا فشل التحويل وكان string فارغ، استخدم array فارغ
                    $existingImages = [];
                }
            }
            
            // تحويل الروابط الكاملة إلى مسارات نسبية
            $convertedImages = [];
            foreach ($existingImages as $imageUrl) {
                $relativePath = $this->convertUrlToRelativePath($imageUrl);
                if ($relativePath !== null) {
                    $convertedImages[] = $relativePath;
                } else {
                    Log::warning('Failed to convert image URL to relative path', [
                        'url' => $imageUrl,
                    ]);
                }
            }
            $existingImages = $convertedImages;
            
            Log::info('Existing images after conversion', [
                'count' => count($existingImages),
                'images' => $existingImages,
                'note' => count($existingImages) === 0 ? 'Empty array - user deleted all existing images' : 'User kept some existing images',
            ]);
        } elseif (!empty($product->images) && is_array($product->images)) {
            // إذا لم يتم إرسال existing_images على الإطلاق، استخدم الصور الحالية
            // (هذا يعني أن المستخدم لم يغير الصور)
            // تحويل الروابط الكاملة إلى مسارات نسبية (في حالة كانت روابط كاملة)
            $convertedImages = [];
            foreach ($product->images as $imageUrl) {
                $relativePath = $this->convertUrlToRelativePath($imageUrl);
                if ($relativePath !== null) {
                    $convertedImages[] = $relativePath;
                } else {
                    // إذا كان المسار نسبي بالفعل، استخدمه كما هو
                    if (strpos($imageUrl, '/uploads/') === 0) {
                        $convertedImages[] = $imageUrl;
                    } else {
                        Log::warning('Could not process image path', ['url' => $imageUrl]);
                    }
                }
            }
            $existingImages = $convertedImages;
            Log::info('Using current product images (no existing_images sent - user did not change images)', [
                'original_count' => count($product->images),
                'converted_count' => count($existingImages),
                'original_images' => $product->images,
                'converted_images' => $existingImages,
            ]);
        } else {
            Log::info('No existing images to process');
        }
        
        // إضافة الصور الجديدة المرفوعة
        if ($request->hasFile('images')) {
            $newImagesCount = 0;
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $filename);
                $existingImages[] = '/uploads/products/' . $filename;
                $newImagesCount++;
            }
            Log::info('New images uploaded', [
                'count' => $newImagesCount,
            ]);
        }

        // حفظ الصور النهائية (الحالية المتبقية + الجديدة)
        $validated['images'] = $existingImages;
        
        Log::info('Final images array before save', [
            'count' => count($validated['images']),
            'images' => $validated['images'],
        ]);

        // حساب sale_price بناءً على نوع الخصم
        if (isset($validated['discount_type']) && $validated['discount_type'] !== 'none' && isset($validated['discount_value']) && $validated['discount_value'] > 0) {
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

        // Log before saving
        Log::info('About to update product', [
            'product_id' => $id,
            'images_to_save' => $validated['images'] ?? null,
            'images_count' => is_array($validated['images'] ?? null) ? count($validated['images']) : 0,
            'main_image' => $validated['main_image'] ?? null,
        ]);

        $product->update($validated);
        
        // Log after saving
        Log::info('Product updated successfully', [
            'product_id' => $id,
            'saved_images' => $product->fresh()->images,
            'saved_images_count' => is_array($product->fresh()->images) ? count($product->fresh()->images) : 0,
        ]);
        
        Log::info('=== Product Update Request Completed ===');

        // تحديث الخصائص
        $product->attributes()->detach(); // حذف الخصائص القديمة

        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attribute) {
                $product->attributes()->attach($attribute['attribute_id'], [
                    'attribute_value_id' => $attribute['attribute_value_id'],
                    'price_adjustment' => $attribute['price_adjustment'] ?? 0
                ]);
            }
        }

        // تحديث الألوان إذا تم تمريرها
        if ($request->has('colors')) {
            $product->colors = $validated['colors'];
            $product->save();
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

        // حذف الصور من public/uploads/products قبل حذف المنتج
        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $imagePath) {
                $fullPath = public_path($imagePath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
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

    /**
     * Show the import form
     */
    public function import()
    {
        $categories = Category::where('status', true)->get(['id', 'name']);
        $brands = Brand::where('status', true)->get(['id', 'name']);

        return Inertia::render('Admin/theme1/Products/Import', [
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'name',
            'name_en',
            'slug',
            'description',
            'short_description',
            'price',
            'sale_price',
            'discount_type',
            'discount_value',
            'stock_quantity',
            'manage_stock',
            'sku',
            'weight',
            'dimensions',
            'category_id',
            'brand_id',
            'is_featured',
            'status',
            'meta_title',
            'meta_description'
        ];

        // Add UTF-8 BOM for proper Arabic support in Excel
        $csvContent = "\xEF\xBB\xBF";
        
        // Add headers
        $csvContent .= implode(',', $headers) . "\n";
        
        // Add sample row with Arabic text
        $csvContent .= "منتج تجريبي,Test Product,test-product,وصف المنتج,وصف مختصر,100,80,percentage,20,50,true,SKU-001,1.5,10x10x5,1,1,false,true,عنوان SEO,وصف SEO\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="products_template.csv"');
    }

    /**
     * Process CSV import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Read file content with UTF-8 encoding
        $content = file_get_contents($path);
        
        // Remove UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // Convert to UTF-8 if not already
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Parse CSV - handle both comma and semicolon delimiters
        $lines = explode("\n", $content);
        $data = [];
        foreach ($lines as $lineIndex => $line) {
            $line = trim($line);
            if ($line !== '') {
                // Try to detect delimiter
                $delimiter = (substr_count($line, ',') > substr_count($line, ';')) ? ',' : ';';
                $row = str_getcsv($line, $delimiter, '"', "\0");
                
                // Clean up the row - remove empty trailing elements
                $row = array_map('trim', $row);
                
                if (!empty(array_filter($row, function($val) { return $val !== ''; }))) {
                    $data[] = $row;
                }
            }
        }

        // Get headers (first row)
        $headers = array_map('trim', $data[0]);
        
        // Remove header row
        array_shift($data);

        $results = [
            'success' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Check if we have data
        if (empty($data)) {
            return redirect()->route('admin.products.import')
                ->with('error', 'الملف فارغ أو لا يحتوي على بيانات صحيحة');
        }

        DB::beginTransaction();
        
        try {
            foreach ($data as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data to associative array
                $rowData = [];
                foreach ($headers as $index => $header) {
                    $value = isset($row[$index]) ? trim($row[$index]) : '';
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    // Normalize boolean values early
                    if (in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off', 'y', 'n'])) {
                        $value = strtolower($value);
                    }
                    $rowData[$header] = $value;
                }

                // Convert numeric fields early for validation
                $rowData['price'] = !empty($rowData['price']) ? (float) str_replace(',', '', $rowData['price']) : 0;
                $rowData['stock_quantity'] = isset($rowData['stock_quantity']) && $rowData['stock_quantity'] !== '' 
                    ? (int) str_replace(',', '', $rowData['stock_quantity']) 
                    : null;

                // Validate required fields
                $missingFields = [];
                if (empty($rowData['name']) || trim($rowData['name']) === '') {
                    $missingFields[] = 'name';
                }
                if (empty($rowData['slug']) || trim($rowData['slug']) === '') {
                    $missingFields[] = 'slug';
                }
                if (empty($rowData['price']) || $rowData['price'] <= 0) {
                    $missingFields[] = 'price (يجب أن يكون أكبر من 0)';
                }
                if ($rowData['stock_quantity'] === null || $rowData['stock_quantity'] < 0) {
                    $missingFields[] = 'stock_quantity (يجب أن يكون رقم صحيح)';
                }
                
                if (!empty($missingFields)) {
                    $results['failed']++;
                    $errorMsg = "الصف {$rowNumber}: الحقول المطلوبة مفقودة أو غير صحيحة: " . implode(', ', $missingFields);
                    $results['errors'][] = $errorMsg;
                    continue;
                }

                // Generate slug if not provided
                if (empty($rowData['slug'])) {
                    $rowData['slug'] = Str::slug($rowData['name']);
                }

                // Check if product exists by SKU first
                $existingProduct = null;
                if (!empty($rowData['sku'])) {
                    $existingProduct = Product::where('sku', $rowData['sku'])->first();
                }

                // Ensure unique slug (only if product doesn't exist or slug is different)
                $slug = $rowData['slug'];
                $counter = 1;
                while (Product::where('slug', $slug)->where(function($query) use ($existingProduct) {
                    if ($existingProduct) {
                        $query->where('id', '!=', $existingProduct->id);
                    }
                })->exists()) {
                    $slug = $rowData['slug'] . '-' . $counter;
                    $counter++;
                }
                $rowData['slug'] = $slug;

                // Convert boolean strings - handle TRUE/FALSE, true/false, 1/0, yes/no
                $manageStock = strtolower(trim($rowData['manage_stock'] ?? 'true'));
                $rowData['manage_stock'] = in_array($manageStock, ['true', '1', 'yes', 'on', 'y'], true);
                
                $isFeatured = strtolower(trim($rowData['is_featured'] ?? 'false'));
                $rowData['is_featured'] = in_array($isFeatured, ['true', '1', 'yes', 'on', 'y'], true);
                
                $status = strtolower(trim($rowData['status'] ?? 'true'));
                $rowData['status'] = in_array($status, ['true', '1', 'yes', 'on', 'y'], true);

                // Convert remaining numeric fields (price and stock_quantity already converted)
                $rowData['sale_price'] = !empty($rowData['sale_price']) ? (float) str_replace(',', '', $rowData['sale_price']) : null;
                $rowData['discount_value'] = !empty($rowData['discount_value']) ? (float) str_replace(',', '', $rowData['discount_value']) : null;
                $rowData['weight'] = !empty($rowData['weight']) ? (float) str_replace(',', '', $rowData['weight']) : null;
                $rowData['category_id'] = !empty($rowData['category_id']) && $rowData['category_id'] !== '' && $rowData['category_id'] !== '0'
                    ? (int) $rowData['category_id'] 
                    : null;
                $rowData['brand_id'] = !empty($rowData['brand_id']) && $rowData['brand_id'] !== '' && $rowData['brand_id'] !== '0'
                    ? (int) $rowData['brand_id'] 
                    : null;

                // Validate category_id exists
                if (!empty($rowData['category_id'])) {
                    $categoryExists = Category::where('id', $rowData['category_id'])->exists();
                    if (!$categoryExists) {
                        $results['failed']++;
                        $errorMsg = "الصف {$rowNumber}: القسم المحدد غير موجود (category_id: {$rowData['category_id']})";
                        $results['errors'][] = $errorMsg;
                        continue;
                    }
                }

                // Validate brand_id exists - if provided, it must exist
                if (!empty($rowData['brand_id']) && $rowData['brand_id'] > 0) {
                    $brandExists = Brand::where('id', $rowData['brand_id'])->exists();
                    if (!$brandExists) {
                        $results['failed']++;
                        $errorMsg = "الصف {$rowNumber}: العلامة التجارية المحددة غير موجودة (brand_id: {$rowData['brand_id']})";
                        $results['errors'][] = $errorMsg;
                        continue;
                    }
                } else {
                    // If brand_id is empty or 0, set it to null
                    $rowData['brand_id'] = null;
                }

                // Calculate sale_price if discount is provided
                if (!empty($rowData['discount_type']) && $rowData['discount_type'] !== 'none' && !empty($rowData['discount_value'])) {
                    if ($rowData['discount_type'] === 'fixed') {
                        $rowData['sale_price'] = max(0, $rowData['price'] - $rowData['discount_value']);
                    } elseif ($rowData['discount_type'] === 'percentage') {
                        $discountAmount = ($rowData['price'] * $rowData['discount_value']) / 100;
                        $rowData['sale_price'] = max(0, $rowData['price'] - $discountAmount);
                    }
                }

                // Set default images as empty array (user will add images manually later)
                // Only if product doesn't exist, otherwise keep existing images
                if (!$existingProduct) {
                    // Ensure images is set as empty array, not null
                    $rowData['images'] = [];
                } else {
                    // Keep existing images when updating - don't override
                    unset($rowData['images']);
                }
                
                // Ensure in_stock is set based on stock_quantity
                $rowData['in_stock'] = ($rowData['stock_quantity'] ?? 0) > 0;

                // If product exists, update it; otherwise create new
                if ($existingProduct) {
                    // Update existing product
                    try {
                        $existingProduct->update($rowData);
                        $results['updated']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $errorMsg = "الصف {$rowNumber}: فشل في تحديث المنتج - " . $e->getMessage();
                        $results['errors'][] = $errorMsg;
                        continue;
                    }
                } else {
                    // Create new product
                    try {
                        // Final validation before create
                        if (empty($rowData['name']) || empty($rowData['slug']) || $rowData['price'] <= 0) {
                            throw new \Exception('بيانات غير صحيحة: name, slug, price مطلوبة');
                        }
                        
                        $product = Product::create($rowData);
                        $results['success']++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $results['failed']++;
                        $errorCode = $e->getCode();
                        if ($errorCode == 23000) { // Duplicate entry
                            $errorMsg = "الصف {$rowNumber}: المنتج موجود مسبقاً (slug أو SKU مكرر)";
                        } else {
                            $errorMsg = "الصف {$rowNumber}: خطأ في قاعدة البيانات - " . $e->getMessage();
                        }
                        $results['errors'][] = $errorMsg;
                        continue;
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $errorMsg = "الصف {$rowNumber}: فشل في إنشاء المنتج - " . $e->getMessage();
                        $results['errors'][] = $errorMsg;
                        continue;
                    }
                }
            }

            DB::commit();

            // Build success message
            $message = '';
            if ($results['success'] > 0) {
                $message .= "تم استيراد {$results['success']} منتج جديد. ";
            }
            if ($results['updated'] > 0) {
                $message .= "تم تحديث {$results['updated']} منتج. ";
            }
            if ($results['failed'] > 0) {
                $message .= "فشل استيراد {$results['failed']} منتج.";
            }
            if (empty($message)) {
                $message = 'لم يتم استيراد أي منتجات. يرجى التحقق من البيانات.';
            }

            // Ensure errors array is always present
            if (!isset($results['errors']) || !is_array($results['errors'])) {
                $results['errors'] = [];
            }

            return redirect()->route('admin.products.import')
                ->with('import_results', $results)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.products.import')
                ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage())
                ->with('import_results', $results);
        }
    }
}
