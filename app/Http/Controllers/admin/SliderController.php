<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Slider::query();

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $sliders = $query->orderBy('sort_order', 'asc')->paginate(10);

        return Inertia::render('Admin/theme1/Sliders/Index', [
            'sliders' => $sliders,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/theme1/Sliders/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|url|max:255',
            'sort_order' => 'required|integer|min:0',
            'status' => 'boolean'
        ]);

        // معالجة الصورة - حفظ في public/uploads/sliders
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            
            // التأكد من وجود المجلد
            $uploadPath = public_path('uploads/sliders');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $image->move($uploadPath, $imageName);
            $validated['image'] = '/uploads/sliders/' . $imageName;
            Log::info('Stored slider image at: ' . $validated['image']);
        }

        Slider::create($validated);

        return redirect()->route('admin.sliders.index')
                        ->with('success', 'تم إنشاء السلايدر بنجاح');
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
        $slider = Slider::findOrFail($id);

        return Inertia::render('Admin/theme1/Sliders/Edit', [
            'slider' => $slider
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $slider = Slider::findOrFail($id);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|url|max:255',
            'sort_order' => 'required|integer|min:0',
            'status' => 'boolean'
        ]);

        // معالجة الصورة الجديدة إذا تم رفعها
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($slider->image) {
                // دعم المسارات القديمة (storage) والجديدة (public)
                $oldPath = $slider->image;
                if (strpos($oldPath, '/storage/') === 0) {
                    // مسار قديم في storage
                    $relativePath = str_replace('/storage/', '', $oldPath);
                    $fullPath = storage_path('app/public/' . $relativePath);
                } else {
                    // مسار جديد في public
                    $fullPath = public_path($oldPath);
                }
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    Log::info('Deleted old slider image: ' . $fullPath);
                }
            }

            // رفع الصورة الجديدة إلى public/uploads/sliders
            $image = $request->file('image');
            $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            
            // التأكد من وجود المجلد
            $uploadPath = public_path('uploads/sliders');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $image->move($uploadPath, $imageName);
            $validated['image'] = '/uploads/sliders/' . $imageName;
            Log::info('Stored new slider image at: ' . $validated['image']);
        }

        $slider->update($validated);

        return redirect()->route('admin.sliders.index')
                        ->with('success', 'تم تحديث السلايدر بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
        
        // حذف الصورة - دعم المسارات القديمة والجديدة
        if ($slider->image) {
            $imagePath = $slider->image;
            
            // دعم المسارات القديمة (storage) والجديدة (public)
            if (strpos($imagePath, '/storage/') === 0) {
                // مسار قديم في storage
                $relativePath = str_replace('/storage/', '', $imagePath);
                $fullPath = storage_path('app/public/' . $relativePath);
            } else {
                // مسار جديد في public
                $fullPath = public_path($imagePath);
            }
            
            if (file_exists($fullPath)) {
                unlink($fullPath);
                Log::info('Deleted slider image: ' . $fullPath);
            }
        }
        
        $slider->delete();

        return redirect()->route('admin.sliders.index')
                        ->with('success', 'تم حذف السلايدر بنجاح');
    }

    /**
     * Toggle slider status
     */
    public function toggleStatus(string $id)
    {
        $slider = Slider::findOrFail($id);
        $slider->update(['status' => !$slider->status]);

        return redirect()->back()->with('success', 'تم تعديل حالة السلايدر');
    }
}
