<?php

namespace App\Http\Controllers\web;

use Inertia\Inertia;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class WebController extends Controller
{
    /**
     * Start Home Page
    **/
    public function home(){
        // Get active sliders ordered by sort_order
        $sliders = Slider::where('status', 'enable') ->orderBy('sort_order', 'asc') ->get();
        // Get active main categories only (parent categories)
        $categories = Category::where('status', 'enable')
            ->whereNull('parent_id')
            ->get();

        return Inertia::render('Front/Theme1/Index', [
            'sliders' => $sliders,
            'categories' => $categories
        ]);
    }
    /**
     * End Home Page
    **/
    public function categories(){
        // Get all active categories for navigation
        $categories = Category::where('status', 'enable')->get();

        return Inertia::render('Front/Theme1/Categories', [
            'categories' => $categories
        ]);
    }

    public function category($id){
        // Find the category by ID and ensure it's active
        $childrens = Category::where('parent_id', $id)->where('status', 'enable')->paginate(12);
        if ($childrens->count() > 0) {
            // القسم له أقسام فرعية، رجع صفحة الأقسام الفرعية
            return Inertia::render('Front/Theme1/Categories', compact('childrens'));
        } else {
            // القسم ليس له أقسام فرعية، رجع صفحة المنتجات تحت القسم
            $products = Product::where('category_id', $id)
                ->where('status', true)
                ->with('category')
                ->paginate(12);
            return Inertia::render('Front/Theme1/Shop', [
                'category' => $id,
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
    }


    public function products(){
        $products = Product::where('status', true)
            ->with('category')
            ->paginate(12);

        return Inertia::render('Front/Theme1/Shop', [
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
    /**
     * Start blog Page
    **/
    public function blog(){
        return Inertia::render('Front/Theme1/Blog');
    }
    /**
     * Start blog Page
    **/

}
