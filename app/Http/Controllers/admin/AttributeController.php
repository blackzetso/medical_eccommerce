<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attribute::with('values');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $attributes = $query->orderBy('sort_order')->orderBy('name')->paginate(10);

        return Inertia::render('Admin/theme1/Attributes/Index', [
            'attributes' => $attributes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/theme1/Attributes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes',
            'type' => 'required|in:select,radio,checkbox',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:255',
            'values.*.label' => 'nullable|string|max:255',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.sort_order' => 'integer|min:0',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $attribute = Attribute::create($validated);

        // Create attribute values
        foreach ($validated['values'] as $valueData) {
            $attribute->values()->create($valueData);
        }

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'تم إنشاء الخاصية بنجاح');
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
        $attribute = Attribute::with('values')->findOrFail($id);

        return Inertia::render('Admin/theme1/Attributes/Edit', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $id,
            'type' => 'required|in:select,radio,checkbox',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'values' => 'required|array|min:1',
            'values.*.id' => 'nullable|integer|exists:attribute_values,id',
            'values.*.value' => 'required|string|max:255',
            'values.*.label' => 'nullable|string|max:255',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.sort_order' => 'integer|min:0',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $attribute->update($validated);

        // Update or create attribute values
        $existingValueIds = [];
        foreach ($validated['values'] as $valueData) {
            if (isset($valueData['id'])) {
                // Update existing value
                $value = AttributeValue::findOrFail($valueData['id']);
                $value->update($valueData);
                $existingValueIds[] = $valueData['id'];
            } else {
                // Create new value
                $newValue = $attribute->values()->create($valueData);
                $existingValueIds[] = $newValue->id;
            }
        }

        // Delete removed values
        $attribute->values()->whereNotIn('id', $existingValueIds)->delete();

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'تم تحديث الخاصية بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'تم حذف الخاصية بنجاح');
    }

    /**
     * Toggle attribute status
     */
    public function toggleStatus(string $id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->update(['status' => !$attribute->status]);

        return redirect()->back()->with('success', 'تم تعديل حالة الخاصية');
    }
}
