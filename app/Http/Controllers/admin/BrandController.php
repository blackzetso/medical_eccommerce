<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
        }

        $brands = $query->paginate(10);

        return Inertia::render('Admin/theme1/Brands/Index', [
            'brands' => $brands,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/theme1/Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'status' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // معالجة اللوجو
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/brands'), $logoName);
            $validated['logo'] = '/uploads/brands/' . $logoName;
        }

        Brand::create($validated);

        return redirect()->route('admin.brands.index')
                        ->with('success', 'تم إنشاء العلامة التجارية بنجاح');
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
        $brand = Brand::findOrFail($id);

        return Inertia::render('Admin/theme1/Brands/Edit', [
            'brand' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $id,
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'status' => 'boolean',
            'existing_logo' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // معالجة اللوجو
        $logo = $validated['existing_logo'] ?? $brand->logo;
        if ($request->hasFile('logo')) {
            // حذف اللوجو القديم إذا كان موجود
            if ($brand->logo && file_exists(public_path($brand->logo))) {
                unlink(public_path($brand->logo));
            }
            $logoFile = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move(public_path('uploads/brands'), $logoName);
            $logo = '/uploads/brands/' . $logoName;
        }
        $validated['logo'] = $logo;

        $brand->update($validated);

        return redirect()->route('admin.brands.index')
                        ->with('success', 'تم تحديث العلامة التجارية بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        
        // حذف اللوجو من storage قبل حذف العلامة التجارية
        if ($brand->logo && $brand->logo !== '/front/theme1/images/no-logo.png') {
            // إزالة /storage/ من المسار للحصول على المسار الحقيقي
            $relativePath = str_replace('/storage/', '', $brand->logo);
            $fullPath = storage_path('app/public/' . $relativePath);
            
            // التحقق من وجود الملف وحذفه
            if (file_exists($fullPath)) {
                unlink($fullPath);
                Log::info('Deleted brand logo: ' . $fullPath);
            } else {
                Log::warning('Brand logo not found for deletion: ' . $fullPath);
            }
        }
        
        $brand->delete();

        return redirect()->route('admin.brands.index')
                        ->with('success', 'تم حذف العلامة التجارية واللوجو بنجاح');
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['status' => !$brand->status]);

        return redirect()->back()->with('success', 'تم تعديل حالة العلامة التجارية');
    }
}
