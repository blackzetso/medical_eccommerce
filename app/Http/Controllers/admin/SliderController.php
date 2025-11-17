<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url|max:255',
            'button_text' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'status' => 'boolean'
        ]);

        // معالجة الصورة
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sliders', 'public');
            $validated['image'] = '/storage/' . $path;
            Log::info('Stored slider image at: ' . $path);
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url|max:255',
            'button_text' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'status' => 'boolean'
        ]);

        // معالجة الصورة الجديدة إذا تم رفعها
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($slider->image) {
                $oldPath = str_replace('/storage/', '', $slider->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('Deleted old slider image: ' . $oldPath);
                }
            }

            // رفع الصورة الجديدة
            $path = $request->file('image')->store('sliders', 'public');
            $validated['image'] = '/storage/' . $path;
            Log::info('Stored new slider image at: ' . $path);
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
        
        // حذف الصورة من storage
        if ($slider->image) {
            $imagePath = str_replace('/storage/', '', $slider->image);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info('Deleted slider image: ' . $imagePath);
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
