<?php

namespace App\Http\Controllers\web;

use Inertia\Inertia;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WebController extends Controller
{
    /**
     * Start Home Page
    **/
    public function home(){
        // Get active sliders ordered by sort_order
        // status is stored as boolean, so we filter by true (1) not string "enable"
        $sliders = Slider::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get(); 
        // Get active main categories only (parent categories)
        $categories = Category::where('status', 'enable')
            ->whereNull('parent_id')
            ->get();

        // تشخيص: إزالة التعليق للتأكد من بيانات الأقسام من السيرفر ([] = فارغ، بيانات = مصدر آخر)
        // dd($categories);

        return Inertia::render('Front/Theme1/Index', [
            'sliders' => $sliders,
            'categories' => $categories
        ]);
    }
    /**
     * End Home Page
    **/
    public function categories(){
        // Get all active categories for navigation (main categories only)
        $childrens = Category::where('status', 'enable')->whereNull('parent_id')->paginate(12);
        return Inertia::render('Front/Theme1/Categories', [
            'childrens' => $childrens
        ]);
    }

    public function category($id, Request $request){
        // Find the category by ID and ensure it's active
        $category = Category::where('id', $id)->where('status', 'enable')->first();
        
        if (!$category) {
            abort(404, 'Category not found');
        }
        
        $childrens = Category::where('parent_id', $id)->where('status', 'enable')->paginate(12);
        if ($childrens->count() > 0) {
            // القسم له أقسام فرعية، رجع صفحة الأقسام الفرعية
            return Inertia::render('Front/Theme1/Categories', [
                'childrens' => $childrens,
                'parent' => $category
            ]);
        } else {
            // القسم ليس له أقسام فرعية، رجع صفحة المنتجات تحت القسم
            
            // Debug: Check total products for this category
            $totalProductsInCategory = Product::where('category_id', $id)
                ->where('status', true)
                ->count();
            
            $products = Product::where('category_id', $id)
                ->where('status', true)
                ->with(['category', 'brand'])
                ->orderByRaw('CASE 
                    WHEN manage_stock = 0 THEN 0 
                    WHEN manage_stock = 1 AND stock_quantity > 0 THEN 0 
                    ELSE 1 
                END ASC')
                ->orderBy('created_at', 'desc')
                ->paginate(12);
                
            // إضافة معايير البحث والفئة إلى pagination links
            $products->appends($request->only(['page']));
            
            // إضافة total_stock لكل منتج (من الخصائص أو المنتج نفسه)
            $productsData = $products->getCollection()->map(function ($product) {
                $productArray = $product->toArray();
                $productArray['total_stock'] = $product->total_stock; // استخدام accessor
                return $productArray;
            });
            
            // استبدال collection في paginator
            $products->setCollection($productsData);
            
            return Inertia::render('Front/Theme1/Shop', [
                'category' => $category,
                'products' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'has_more' => $products->hasMorePages()
                ]
            ]);
        }
    }


    public function products(Request $request){
        $query = Product::where('status', true)
            ->with('category');
        
        // Handle search query
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
            
            // Order by stock availability: products with stock > 0 first, then out of stock
            $query->orderByRaw('CASE 
                WHEN manage_stock = 0 THEN 0 
                WHEN manage_stock = 1 AND stock_quantity > 0 THEN 0 
                ELSE 1 
            END ASC')
            ->orderBy('created_at', 'desc');
        } else {
            // If no search, just order by created_at
            $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(12);

        // إضافة معايير البحث إلى pagination links
        $products->appends($request->only(['search']));

        // إضافة total_stock لكل منتج (من الخصائص أو المنتج نفسه)
        $productsData = $products->getCollection()->map(function ($product) {
            $productArray = $product->toArray();
            $productArray['total_stock'] = $product->total_stock; // استخدام accessor
            return $productArray;
        });

        // استبدال collection في paginator
        $products->setCollection($productsData);

        return Inertia::render('Front/Theme1/Shop', [
            'products' => $products->items(),
            'search' => $request->search ?? '',
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
                'has_more' => $products->hasMorePages()
            ]
        ]);
    }
    /**
     * Start Lessons Page
    **/
    public function lessons(){
        return Inertia::render('Front/Theme1/Lessons');
    }
    /**
     * End lessons Page
    **/
    /**
     * Start teachers Page
    **/
    public function teachers(){
        return Inertia::render('Front/Theme1/Teachers');
    }
    /**
     * End teachers Page
    **/
    /**
     * Start Shop Page
    **/
    public function shop($categoryId = null){
        $category = null;
        $query = Product::where('status', true);

        if ($categoryId) {
            $category = Category::where('id', $categoryId)->where('status', 'enable')->first();
            if (!$category) {
                abort(404, 'Category not found');
            }
            $query->where('category_id', $categoryId);
        }

        $products = $query->with(['category'])->paginate(12);
        $categories = Category::where('status', 'enable')->get();

        // إضافة total_stock لكل منتج (من الخصائص أو المنتج نفسه)
        $productsData = $products->getCollection()->map(function ($product) {
            $productArray = $product->toArray();
            $productArray['total_stock'] = $product->total_stock; // استخدام accessor
            return $productArray;
        });

        // استبدال collection في paginator
        $products->setCollection($productsData);

        return Inertia::render('Front/Theme1/Shop', [
            'category' => $category,
            'categories' => $categories,
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }
    /**
     * End Shop Page
    **/

    public function product($id){
        $product = Product::with(['category'])->findOrFail($id);

        // جلب الخصائص مع القيم المحددة فقط للمنتج من pivot table
        $productAttributes = DB::table('product_attributes')
            ->where('product_id', $id)
            ->join('attributes', 'product_attributes.attribute_id', '=', 'attributes.id')
            ->join('attribute_values', 'product_attributes.attribute_value_id', '=', 'attribute_values.id')
            ->select(
                'attributes.id as attribute_id',
                'attributes.name as attribute_name',
                'attributes.slug as attribute_slug',
                'attributes.type as attribute_type',
                'attribute_values.id as value_id',
                'attribute_values.value as value_value',
                'attribute_values.label as value_label',
                'attribute_values.color_code as value_color_code',
                'attribute_values.image as value_image',
                'product_attributes.price_adjustment as price',
                'product_attributes.stock_quantity as stock_quantity'
            )
            ->orderBy('attributes.sort_order')
            ->orderBy('attribute_values.sort_order')
            ->get();

        // تجميع البيانات حسب الـ attribute
        $groupedAttributes = [];
        foreach ($productAttributes as $row) {
            $attrId = $row->attribute_id;
            if (!isset($groupedAttributes[$attrId])) {
                $groupedAttributes[$attrId] = [
                    'id' => $attrId,
                    'name' => $row->attribute_name,
                    'slug' => $row->attribute_slug,
                    'type' => $row->attribute_type,
                    'values' => []
                ];
            }

            $groupedAttributes[$attrId]['values'][] = [
                'id' => $row->value_id,
                'attribute_id' => $attrId,
                'value' => $row->value_value,
                'label' => $row->value_label,
                'color_code' => $row->value_color_code,
                'image' => $row->value_image,
                'price' => floatval($row->price ?? 0),
                'stock_quantity' => intval($row->stock_quantity ?? 0)
            ];
        }

        // تحويل إلى array indexed
        $product->attributes = array_values($groupedAttributes);

        return Inertia::render('Front/Theme1/ProductDetails', [
            'product' => $product
        ]);
    }

    /**
     * Start blog Page
     **/
    public function blog(){
        return Inertia::render('Front/Theme1/Blog');
    }
    /**
     * Start blog Page
     **/

    /**
     * Start Contact Page
     **/
    public function contact(){
        return Inertia::render('Front/Theme1/Contact');
    }
    /**
     * End Contact Page
     **/

    /**
     * Submit Contact Form
     **/
    public function submitContact(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        // Save to database
        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'is_read' => false,
        ]);

        // Send email to admin
        try {
            $adminEmail = Setting::get('site_email', config('mail.from.address'));
            
            Mail::send([], [], function ($message) use ($request, $adminEmail) {
                $message->to($adminEmail)
                    ->subject('رسالة تواصل جديدة: ' . ($request->subject ?? 'بدون موضوع'))
                    ->html("
                        <h2>رسالة تواصل جديدة</h2>
                        <p><strong>الاسم:</strong> {$request->name}</p>
                        <p><strong>البريد الإلكتروني:</strong> {$request->email}</p>
                        " . ($request->subject ? "<p><strong>الموضوع:</strong> {$request->subject}</p>" : "") . "
                        <p><strong>الرسالة:</strong></p>
                        <p>" . nl2br(e($request->message)) . "</p>
                    ");
            });
        } catch (\Exception $e) {
            // Continue even if email fails
        }

        return back()->with('success', 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.');
    }
    /**
     * End Submit Contact Form
     **/

    /**
     * صفحة من نحن
     **/
    public function about(){
        $content = Setting::get('page_about', '');
        return Inertia::render('Front/Theme1/About', [
            'content' => $content
        ]);
    }

    /**
     * صفحة سياسة الخصوصية
     **/
    public function privacy(){
        $content = Setting::get('page_privacy', '');
        return Inertia::render('Front/Theme1/Privacy', [
            'content' => $content
        ]);
    }

    /**
     * صفحة شروط الاستخدام
     **/
    public function terms(){
        $content = Setting::get('page_terms', '');
        return Inertia::render('Front/Theme1/Terms', [
            'content' => $content
        ]);
    }

    /**
     * صفحة سياسة الاسترداد
     **/
    public function refund(){
        $content = Setting::get('page_refund', '');
        return Inertia::render('Front/Theme1/Refund', [
            'content' => $content
        ]);
    }
}
