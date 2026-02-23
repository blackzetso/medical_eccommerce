<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LeadController extends Controller
{
    /**
     * عرض قائمة الـ leads
     */
    public function index(Request $request)
    {
        $query = Lead::with('convertedBy')
            ->orderBy('created_at', 'desc');

        // البحث حسب الاسم أو البريد الإلكتروني أو الهاتف
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('pharmacy_name', 'like', "%{$search}%");
            });
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->paginate(15);

        return Inertia::render('Admin/theme1/Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * عرض تفاصيل lead
     */
    public function show($id)
    {
        $lead = Lead::with('convertedBy')->findOrFail($id);

        return Inertia::render('Admin/theme1/Leads/Show', [
            'lead' => $lead,
        ]);
    }

    /**
     * تحويل lead إلى user
     */
    public function convertToUser(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        // التحقق من أن الـ lead لم يتم تحويله من قبل
        if ($lead->status === 'converted') {
            return back()->withErrors(['error' => 'تم تحويل هذا الـ lead من قبل']);
        }

        $request->validate([
            'orgasoft_id' => 'required|string',
            'password' => 'required|min:6',
        ]);

        // إنشاء user جديد
        $user = User::create([
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'client',
            'location_url' => $lead->location_url,
            'orgasoft_id' => $request->orgasoft_id,
            'email_verified_at' => now(),
        ]);

        // تحديث حالة الـ lead
        $lead->update([
            'status' => 'converted',
            'converted_by' => auth()->id(),
            'converted_at' => now(),
        ]);

        return redirect()->route('admin.leads.index')
            ->with('success', 'تم تحويل الـ lead إلى حساب عميل بنجاح');
    }

    /**
     * تحديث حالة الـ lead
     */
    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,contacted,converted,rejected',
        ]);

        $lead->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'تم تحديث حالة الـ lead بنجاح');
    }

    /**
     * حذف lead
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'تم حذف الـ lead بنجاح');
    }
}
