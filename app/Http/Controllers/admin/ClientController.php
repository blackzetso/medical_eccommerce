<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * عرض قائمة العملاء
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'client')
            ->withCount(['orders'])
            ->orderBy('created_at', 'desc');

        // البحث حسب الاسم أو البريد الإلكتروني
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        $clients = $query->paginate(15);

        // حساب إجمالي المشتريات لكل عميل
        $clients->getCollection()->transform(function ($client) {
            $client->total_spent = Order::where('user_id', $client->id)
                ->where('payment_status', 'paid')
                ->sum('total_amount');
            $client->total_orders_count = Order::where('user_id', $client->id)->count();
            return $client;
        });

        return Inertia::render('Admin/theme1/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * عرض تفاصيل العميل
     */
    public function show($id)
    {
        $client = User::where('user_type', 'client')
            ->select(['id', 'name', 'email', 'phone', 'location_url', 'email_verified_at', 'created_at'])
            ->with(['orders' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])
            ->findOrFail($id);

        // البحث عن lead المرتبط بالعميل باستخدام البريد الإلكتروني
        $lead = Lead::where('email', $client->email)
            ->orderBy('created_at', 'desc')
            ->first();

        $statistics = $this->getStatistics($id);

        return Inertia::render('Admin/theme1/Clients/Show', [
            'client' => $client,
            'statistics' => $statistics,
            'lead' => $lead ? [
                'address' => $lead->address,
                'location_url' => $lead->location_url,
                'pharmacy_name' => $lead->pharmacy_name,
            ] : null,
        ]);
    }

    /**
     * عرض نموذج تعديل العميل
     */
    public function edit($id)
    {
        $client = User::where('user_type', 'client')->findOrFail($id);

        return Inertia::render('Admin/theme1/Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * تحديث بيانات العميل
     */
    public function update(Request $request, $id)
    {
        $client = User::where('user_type', 'client')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $client->update($data);

        return redirect()->route('admin.clients.show', $id)
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    /**
     * تفعيل/تعطيل حساب العميل
     */
    public function toggleStatus($id)
    {
        $client = User::where('user_type', 'client')->findOrFail($id);

        // استخدام email_verified_at كحالة الحساب
        if ($client->email_verified_at) {
            $client->email_verified_at = null;
            $message = 'تم تعطيل حساب العميل بنجاح';
        } else {
            $client->email_verified_at = now();
            $message = 'تم تفعيل حساب العميل بنجاح';
        }

        $client->save();

        return back()->with('success', $message);
    }

    /**
     * إحصائيات العميل
     */
    public function getStatistics($id)
    {
        $client = User::where('user_type', 'client')->findOrFail($id);

        $orders = Order::where('user_id', $id)->get();

        $statistics = [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'average_order_value' => $orders->where('payment_status', 'paid')->avg('total_amount') ?? 0,
            'last_order_date' => $orders->max('created_at'),
            'first_order_date' => $orders->min('created_at'),
        ];

        return $statistics;
    }
}

