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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
        // Use a safer regex pattern that avoids issues with special characters
        if (preg_match('|(/uploads/products/[^?#]+)|', $url, $matches)) {
            return $matches[1];
        }
        
        // Also check for /uploads/ in general (for other upload paths)
        if (preg_match('|(/uploads/[^?#]+)|', $url, $matches)) {
            return $matches[1];
        }

        // If all else fails, return null
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
            
            // تقسيم مصطلح البحث إلى كلمات منفصلة
            $searchWords = preg_split('/\s+/', $searchTerm, -1, PREG_SPLIT_NO_EMPTY);
            
            $query->where(function($q) use ($searchWords, $searchTerm) {
                // البحث في SKU أو كود المنتج (مطابقة كاملة أو جزئية)
                $q->where(function($subQ) use ($searchTerm) {
                    $subQ->where('sku', 'like', '%' . $searchTerm . '%')
                         ->orWhere('product_code', 'like', '%' . $searchTerm . '%');
                });
                
                // أو البحث في اسم المنتج (جميع الكلمات يجب أن تظهر في الاسم)
                if (count($searchWords) > 0) {
                    $q->orWhere(function($nameQ) use ($searchWords) {
                        // كل كلمة من كلمات البحث يجب أن تظهر في الاسم (عربي أو إنجليزي)
                        foreach ($searchWords as $word) {
                            $nameQ->where(function($wordQ) use ($word) {
                                $wordQ->where('name', 'like', '%' . $word . '%')
                                      ->orWhere('name_en', 'like', '%' . $word . '%');
                            });
                        }
                    });
                }
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        // تعديل الصور لإرسال رابط كامل وإضافة total_stock
        $products->getCollection()->transform(function ($product) {
            // تعديل main_image إذا كان موجوداً
            if ($product->main_image) {
                $product->main_image = asset('/' . ltrim($product->main_image, '/'));
            }
            // تعديل صور المصفوفة
            if (is_array($product->images)) {
                $product->images = array_map(fn($img) => asset('/' . ltrim($img, '/')), $product->images);
            }
            // إضافة total_stock (إجمالي المخزون)
            $product->total_stock = $product->total_stock;
            // إضافة has_attributes للتحقق من وجود خصائص
            $product->has_attributes = $product->hasAttributes();
            return $product;
        });

        $categories = Category::where('status', true)->get();
        $brands = Brand::where('status', true)->get();

        // إضافة معايير البحث إلى pagination links
        $products->appends($request->only(['search']));
        
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
            'cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:1000',
            'price' => 'nullable|numeric|min:0', // Will be calculated automatically
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => $request->filled('discount_type') ? 'required|in:none,fixed,percentage' : 'nullable',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0', // Only used if no attributes
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
            'attributes.*.stock_quantity' => 'nullable|integer|min:0',
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

        // حساب السعر من التكلفة وهامش الربح
        $cost = $validated['cost'];
        $profitMargin = $validated['profit_margin'];
        $validated['price'] = $cost * (1 + $profitMargin / 100);

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

        // إذا كان المنتج له خصائص، لا نحتاج stock_quantity على مستوى المنتج
        $hasAttributes = $request->has('attributes') && is_array($request->input('attributes')) && count($request->input('attributes')) > 0;
        if ($hasAttributes) {
            // تجاهل stock_quantity على مستوى المنتج إذا كان هناك خصائص
            $validated['stock_quantity'] = 0;
        } else {
            // إذا لم يكن هناك خصائص، stock_quantity مطلوب
            if (!isset($validated['stock_quantity']) || $validated['stock_quantity'] === null) {
                $validated['stock_quantity'] = 0;
            }
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
                    'price_adjustment' => $attribute['price_adjustment'] ?? 0,
                    'stock_quantity' => $attribute['stock_quantity'] ?? 0
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
                'price_adjustment' => $attribute->pivot->price_adjustment,
                'stock_quantity' => $attribute->pivot->stock_quantity ?? 0
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
        
        // تحويل existing_images من JSON string إلى array إذا لزم الأمر
        if (isset($requestData['existing_images'])) {
            if (is_string($requestData['existing_images'])) {
                $decoded = json_decode($requestData['existing_images'], true);
                if (is_array($decoded)) {
                    $requestData['existing_images'] = $decoded;
                } else {
                    $requestData['existing_images'] = [];
                }
            } elseif (!is_array($requestData['existing_images'])) {
                // إذا لم تكن string ولا array، اجعلها array فارغ
                $requestData['existing_images'] = [];
            }
        } else {
            // إذا لم يتم إرسال existing_images على الإطلاق، اجعلها array فارغ
            $requestData['existing_images'] = [];
        }

        // تحويل existing_main_image إلى مسار نسبي إذا كانت URL كاملة
        if (!empty($requestData['existing_main_image'])) {
            $convertedMain = $this->convertUrlToRelativePath($requestData['existing_main_image']);
            $requestData['existing_main_image'] = $convertedMain ?? $requestData['existing_main_image'];
        }
        
        // استبدال request data
        $request->merge($requestData);

        try {
            $validated = $request->validate([
            'name' => $request->input('name') ? 'required|string|max:255' : 'nullable',
            'name_en' => 'nullable|string|max:255',
            'slug' => $request->input('slug') ? 'required|string|max:255|unique:products,slug,' . $id : 'nullable',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:1000',
            'price' => 'nullable|numeric|min:0', // Will be calculated automatically
            'sale_price' => 'nullable|numeric|min:0',
            'discount_type' => $request->filled('discount_type') ? 'required|in:none,fixed,percentage' : 'nullable',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0', // Only used if no attributes
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
            'existing_images' => 'nullable|array',
            'images' => 'nullable|array', // Accept existing images if no new images are uploaded
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'required_with:attributes|exists:attribute_values,id',
            'attributes.*.price_adjustment' => 'nullable|numeric',
            'attributes.*.stock_quantity' => 'nullable|integer|min:0',
            'colors' => 'nullable|array', // Validate colors as an optional array
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        // معالجة الصورة الرئيسية ورفعها إلى public/uploads/products
        if ($request->hasFile('main_image')) {
            // حذف الصورة الرئيسية القديمة إذا كانت موجودة
            if ($product->main_image && file_exists(public_path($product->main_image))) {
                @unlink(public_path($product->main_image));
            }
            
            $mainImage = $request->file('main_image');
            $mainImageFilename = uniqid() . '_main_' . time() . '.' . $mainImage->getClientOriginalExtension();
            $mainImage->move(public_path('uploads/products'), $mainImageFilename);
            $validated['main_image'] = '/uploads/products/' . $mainImageFilename;
        } else {
            // الاحتفاظ بالصورة الرئيسية الحالية إذا لم يتم رفع صورة جديدة
            if (!empty($validated['existing_main_image'])) {
                $convertedMain = $this->convertUrlToRelativePath($validated['existing_main_image']);
                $validated['main_image'] = $convertedMain ?? $validated['existing_main_image'];
            } elseif (!empty($product->main_image)) {
                $validated['main_image'] = $product->main_image;
            } else {
                $validated['main_image'] = null;
            }
        }

        // معالجة الصور الإضافية - الحصول على الصور الحالية المتبقية
        $existingImages = [];
        
        // التحقق من وجود existing_images (حتى لو كانت فارغة)
        // يمكن أن تكون array أو JSON string
        $hasExistingImages = $request->has('existing_images') || 
                           $request->input('existing_images') !== null;
        
        // إذا تم إرسال existing_images (حتى لو كانت فارغة)، استخدمها
        // هذا مهم: إذا أرسل المستخدم array فارغ، يعني أنه حذف جميع الصور القديمة
        if ($hasExistingImages) {
            $existingImagesInput = $request->input('existing_images');
            
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
                if (!empty($imageUrl)) {
                    $relativePath = $this->convertUrlToRelativePath($imageUrl);
                    if ($relativePath !== null) {
                        $convertedImages[] = $relativePath;
                    } elseif (strpos($imageUrl, '/uploads/') === 0) {
                        // إذا كان المسار نسبي بالفعل، استخدمه كما هو
                        $convertedImages[] = $imageUrl;
                    }
                }
            }
            $existingImages = $convertedImages;
        } elseif (!empty($product->images) && is_array($product->images)) {
            // إذا لم يتم إرسال existing_images على الإطلاق، استخدم الصور الحالية
            // (هذا يعني أن المستخدم لم يغير الصور)
            // تحويل الروابط الكاملة إلى مسارات نسبية (في حالة كانت روابط كاملة)
            $convertedImages = [];
            foreach ($product->images as $imageUrl) {
                if (!empty($imageUrl)) {
                    $relativePath = $this->convertUrlToRelativePath($imageUrl);
                    if ($relativePath !== null) {
                        $convertedImages[] = $relativePath;
                    } elseif (strpos($imageUrl, '/uploads/') === 0) {
                        // إذا كان المسار نسبي بالفعل، استخدمه كما هو
                        $convertedImages[] = $imageUrl;
                    }
                }
            }
            $existingImages = $convertedImages;
        }
        
        // إضافة الصور الجديدة المرفوعة
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/products'), $filename);
                    $existingImages[] = '/uploads/products/' . $filename;
                }
            }
        }

        // حفظ الصور النهائية (الحالية المتبقية + الجديدة)
        $validated['images'] = $existingImages;

        // حساب السعر من التكلفة وهامش الربح
        if (isset($validated['cost']) && isset($validated['profit_margin'])) {
            $cost = $validated['cost'];
            $profitMargin = $validated['profit_margin'];
            $validated['price'] = $cost * (1 + $profitMargin / 100);
        }

        // حساب sale_price بناءً على نوع الخصم
        if (isset($validated['discount_type']) && $validated['discount_type'] !== 'none' && isset($validated['discount_value']) && $validated['discount_value'] > 0) {
            $price = $validated['price'] ?? 0;
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

        // إذا كان المنتج له خصائص، لا نحتاج stock_quantity على مستوى المنتج
        $hasAttributes = $request->has('attributes') && is_array($request->input('attributes')) && count($request->input('attributes')) > 0;
        if ($hasAttributes) {
            // تجاهل stock_quantity على مستوى المنتج إذا كان هناك خصائص
            $validated['stock_quantity'] = 0;
        } else {
            // إذا لم يكن هناك خصائص، stock_quantity مطلوب
            if (!isset($validated['stock_quantity']) || $validated['stock_quantity'] === null) {
                $validated['stock_quantity'] = 0;
            }
        }

        try {
            $product->update($validated);
        } catch (\Exception $e) {
            throw $e;
        }

        // تحديث الخصائص
        $product->attributes()->detach(); // حذف الخصائص القديمة

        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            foreach ($request->input('attributes') as $attribute) {
                $product->attributes()->attach($attribute['attribute_id'], [
                    'attribute_value_id' => $attribute['attribute_value_id'],
                    'price_adjustment' => $attribute['price_adjustment'] ?? 0,
                    'stock_quantity' => $attribute['stock_quantity'] ?? 0
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
    public function destroy(Request $request, string $id)
    {
        // التحقق من كلمة المرور
        $request->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'كلمة المرور غير صحيحة']);
        }

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
     * Add stock quantity to product
     */
    public function addStockQuantity(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($id);
        
        // إضافة الكمية الجديدة للكمية الحالية
        $product->stock_quantity = ($product->stock_quantity ?? 0) + $validated['quantity'];
        
        // تحديث حالة التوفر
        $product->in_stock = $product->stock_quantity > 0;
        
        $product->save();

        return redirect()->back()->with('success', 'تم إضافة الكمية بنجاح');
    }

    /**
     * Update product cost and profit margin
     */
    public function updateCostAndMargin(Request $request, string $id)
    {
        $validated = $request->validate([
            'cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:1000',
        ]);

        $product = Product::findOrFail($id);
        
        // تحديث التكلفة وهامش الربح
        $product->cost = $validated['cost'];
        $product->profit_margin = $validated['profit_margin'];
        
        // حساب السعر تلقائياً: price = cost * (1 + profit_margin / 100)
        $product->price = $validated['cost'] * (1 + $validated['profit_margin'] / 100);
        
        // إعادة حساب sale_price إذا كان هناك خصم
        if ($product->discount_type !== 'none' && $product->discount_value > 0) {
            if ($product->discount_type === 'fixed') {
                $product->sale_price = max(0, $product->price - $product->discount_value);
            } elseif ($product->discount_type === 'percentage') {
                $discountAmount = ($product->price * $product->discount_value) / 100;
                $product->sale_price = max(0, $product->price - $discountAmount);
            }
        } else {
            $product->sale_price = null;
        }
        
        $product->save();

        return back()->with('success', 'تم تحديث التكلفة وهامش الربح بنجاح');
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
