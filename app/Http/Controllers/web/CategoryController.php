<?php

namespace App\Http\Controllers\web;

use Inertia\inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function category(string $id)
    {
    
        $category = Category::findOrFail($id);
        $children = $category->children()->where('status', 'enable')->get();

        if ($children->count() > 0) {
            // يوجد أقسام فرعية، اعرضها
            return inertia('Front/Theme1/Categories', [
                'categories' => [
                    'data' => $children,
                ],
                'parent' => $category,
            ]);
        } else {
            // لا يوجد أقسام فرعية، انتقل إلى صفحة shop
            return redirect()->route('web.shop', ['categoryId' => $category->id]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
