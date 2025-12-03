<?php

namespace App\Http\Controllers\admin;

use inertia\inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('parent')->orderBy('id', 'DESC')->paginate(10);
        return inertia::render('Admin/theme1/Categories/Index',compact('categories'));
    }

    public function search($phrase, Request $request)
    {
        $categories = Category::where('name', 'like', '%' . $phrase . '%')
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return inertia::render('Admin/theme1/Categories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $phrase],
        ]);
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->status = $category->status === 'enable' ? 'disable' : 'enable';
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث الحالة بنجاح');
    }

    /**
     * Show the category for creating a new resource.
     */
    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return Inertia::render('Admin/theme1/Categories/Create', [
            'categories' => $categories,
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // رفع الصورة إذا كانت موجودة
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = '/uploads/categories/' . $imageName;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'تم إضافة القسم بنجاح');
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the category for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return inertia::render('Admin/theme1/Categories/Edit', [
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // ممنوع القسم يبقى أب لنفسه
        if ($data['parent_id'] == $id) {
            return back()->withErrors(['parent_id' => 'لا يمكن اختيار نفس القسم كقسم أب.']);
        }

        $category = Category::findOrFail($id);

        // رفع الصورة الجديدة إذا كانت موجودة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = '/uploads/categories/' . $imageName;
        }

        $category->update([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?: null, // لو parent_id فاضي → null
            'image' => $data['image'] ?? $category->image, // احتفظ بالصورة القديمة إذا لم يتم رفع صورة جديدة
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم تعديل القسم بنجاح');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        
        // حذف الصورة إذا كانت موجودة
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }
        
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم حذف النموذج بنجاح');
    }
}
