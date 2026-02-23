<?php

namespace App\Http\Controllers\admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * الإعدادات العامة
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return Inertia::render('Admin/theme1/Settings/Index', [
            'settings' => $settings
        ]);
    }

    /**
     * إعدادات المظهر
     */
    public function appearance()
    {
        $settings = Setting::whereIn('key', [
            'site_logo',
            'site_favicon',
            'primary_color',
            'secondary_color',
            'theme_mode'
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Appearance', [
            'settings' => $settings
        ]);
    }

    /**
     * إعدادات البريد الإلكتروني
     */
    public function email()
    {
        $settings = Setting::whereIn('key', [
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name'
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Email', [
            'settings' => $settings
        ]);
    }

    /**
     * إعدادات الدفع
     */
    public function payment()
    {
        $settings = Setting::whereIn('key', [
            'payment_method',
            'stripe_key',
            'stripe_secret',
            'paypal_client_id',
            'paypal_secret',
            'cash_on_delivery_enabled'
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Payment', [
            'settings' => $settings
        ]);
    }

    /**
     * إعدادات الشحن
     */
    public function shipping()
    {
        $settings = Setting::whereIn('key', [
            'shipping_method',
            'shipping_cost',
            'free_shipping_threshold',
            'shipping_zones'
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Shipping', [
            'settings' => $settings
        ]);
    }

    /**
     * إعدادات الإشعارات
     */
    public function notification()
    {
        $settings = Setting::whereIn('key', [
            'email_notifications',
            'sms_notifications',
            'order_notifications',
            'payment_notifications',
            'admin_email'
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Notification', [
            'settings' => $settings
        ]);
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        // Settings that should not be saved (static settings)
        $excludedKeys = [];

        foreach ($request->settings as $key => $value) {
            // Skip excluded keys and null values (but allow empty strings for text fields)
            if (in_array($key, $excludedKeys) || $value === null) {
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    /**
     * صفحة من نحن
     */
    public function about()
    {
        $content = Setting::get('page_about', '');
        return Inertia::render('Admin/theme1/Settings/About', [
            'content' => $content
        ]);
    }

    /**
     * صفحة سياسة الخصوصية
     */
    public function privacy()
    {
        $content = Setting::get('page_privacy', '');
        return Inertia::render('Admin/theme1/Settings/Privacy', [
            'content' => $content
        ]);
    }

    /**
     * صفحة شروط الاستخدام
     */
    public function terms()
    {
        $content = Setting::get('page_terms', '');
        return Inertia::render('Admin/theme1/Settings/Terms', [
            'content' => $content
        ]);
    }

    /**
     * صفحة سياسة الاسترداد
     */
    public function refund()
    {
        $content = Setting::get('page_refund', '');
        return Inertia::render('Admin/theme1/Settings/Refund', [
            'content' => $content
        ]);
    }

    /**
     * تحديث محتوى صفحة من نحن
     */
    public function updateAbout(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'page_about'],
            ['value' => $request->content]
        );

        return back()->with('success', 'تم تحديث محتوى صفحة من نحن بنجاح');
    }

    /**
     * تحديث محتوى صفحة سياسة الخصوصية
     */
    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'page_privacy'],
            ['value' => $request->content]
        );

        return back()->with('success', 'تم تحديث محتوى صفحة سياسة الخصوصية بنجاح');
    }

    /**
     * تحديث محتوى صفحة شروط الاستخدام
     */
    public function updateTerms(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'page_terms'],
            ['value' => $request->content]
        );

        return back()->with('success', 'تم تحديث محتوى صفحة شروط الاستخدام بنجاح');
    }

    /**
     * تحديث محتوى صفحة سياسة الاسترداد
     */
    public function updateRefund(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'page_refund'],
            ['value' => $request->content]
        );

        return back()->with('success', 'تم تحديث محتوى صفحة سياسة الاسترداد بنجاح');
    }

    /**
     * إعدادات التكامل مع سيستم الديسكتوب OrgaSoft
     */
    public function integration()
    {
        $settings = Setting::whereIn('key', [
            'orgasoft_enabled',
            'orgasoft_url',
            'orgasoft_api_key',
            'orgasoft_account_id',
        ])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/theme1/Settings/Integration', [
            'settings' => $settings,
        ]);
    }

    /**
     * تحديث إعدادات التكامل مع OrgaSoft
     */
    public function updateIntegration(Request $request)
    {
        $request->validate([
            'settings'                   => 'required|array',
            'settings.orgasoft_url'      => 'required|url',
            'settings.orgasoft_api_key'  => 'required|string',
            'settings.orgasoft_account_id' => 'nullable|string',
            'settings.orgasoft_enabled'  => 'nullable',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return back()->with('success', 'تم تحديث إعدادات التكامل بنجاح');
    }
}
