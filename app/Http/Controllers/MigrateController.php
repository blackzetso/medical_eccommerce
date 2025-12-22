<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\PermissionSeeder;

class MigrateController extends Controller
{
    /**
     * كلمة المرور المطلوبة للوصول للصفحة
     * يمكن تغييرها من .env
     */
    private function getPassword()
    {
        return env('MIGRATE_PAGE_PASSWORD', 'migrate123');
    }

    /**
     * عرض صفحة migrate
     */
    public function showPage(Request $request)
    {
        // التحقق من كلمة المرور في session
        if (!$request->session()->has('migrate_authenticated')) {
            return view('migrate.login');
        }

        return view('migrate.index');
    }

    /**
     * التحقق من كلمة المرور
     */
    public function authenticate(Request $request)
    {
        $password = $request->input('password');
        $correctPassword = $this->getPassword();

        if ($password === $correctPassword) {
            $request->session()->put('migrate_authenticated', true);
            return redirect()->route('migrate.page')->with('success', 'تم التحقق من كلمة المرور بنجاح');
        }

        return back()->with('error', 'كلمة المرور غير صحيحة');
    }

    /**
     * تنفيذ migrate
     */
    public function runMigrate(Request $request)
    {
        // التحقق من كلمة المرور
        if (!$request->session()->has('migrate_authenticated')) {
            return response()->json(['error' => 'غير مصرح'], 401);
        }

        try {
            // الحصول على migrations الحالية قبل التنفيذ
            $migrationsBefore = DB::table('migrations')->pluck('migration')->toArray();

            // تنفيذ migrate
            Artisan::call('migrate', ['--force' => true]);

            // الحصول على output من Artisan
            $output = Artisan::output();

            // الحصول على migrations الجديدة بعد التنفيذ
            $migrationsAfter = DB::table('migrations')->pluck('migration')->toArray();
            $newMigrations = array_diff($migrationsAfter, $migrationsBefore);

            // استخراج أسماء الجداول من migrations الجديدة
            $tables = [];
            foreach ($newMigrations as $migration) {
                // محاولة استخراج اسم الجدول من اسم migration
                // مثال: 2025_11_05_181909_create_products_table -> products
                if (preg_match('/create_(\w+)_table/', $migration, $matches)) {
                    $tables[] = $matches[1];
                } elseif (preg_match('/add_(\w+)_to_(\w+)_table/', $migration, $matches)) {
                    $tables[] = $matches[2] . ' (تمت إضافة ' . $matches[1] . ')';
                } elseif (preg_match('/_to_(\w+)_table/', $migration, $matches)) {
                    $tables[] = $matches[1];
                }
            }

            // إذا لم نجد جداول من الاسم، نستخدم اسم migration
            if (empty($tables) && !empty($newMigrations)) {
                $tables = $newMigrations;
            }

            return response()->json([
                'success' => true,
                'output' => $output,
                'tables' => $tables,
                'migrations_count' => count($newMigrations),
                'message' => count($newMigrations) > 0 
                    ? 'تم تنفيذ ' . count($newMigrations) . ' migration بنجاح' 
                    : 'جميع migrations محدثة بالفعل'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'output' => Artisan::output()
            ], 500);
        }
    }

    /**
     * تنفيذ seed للصلاحيات والرولز
     */
    public function runSeed(Request $request)
    {
        // التحقق من كلمة المرور
        if (!$request->session()->has('migrate_authenticated')) {
            return response()->json(['error' => 'غير مصرح'], 401);
        }

        try {
            // التحقق من وجود الجداول المطلوبة
            $requiredTables = ['permissions', 'roles', 'role_has_permissions'];
            $missingTables = [];

            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (!empty($missingTables)) {
                $missingTablesList = implode(', ', $missingTables);
                return response()->json([
                    'success' => false,
                    'error' => 'الجداول المطلوبة غير موجودة',
                    'output' => "⚠️ خطأ: الجداول التالية غير موجودة: {$missingTablesList}\n\n" .
                               "يرجى تشغيل Migrate أولاً لإنشاء الجداول المطلوبة.\n" .
                               "اضغط على زر 'تنفيذ Migrate' قبل تشغيل Seed."
                ], 400);
            }

            // الحصول على عدد الصلاحيات والرولز قبل التنفيذ
            $permissionsBefore = DB::table('permissions')->count();
            $rolesBefore = DB::table('roles')->count();

            // تنفيذ PermissionSeeder
            $seeder = new PermissionSeeder();
            $seeder->run();

            // الحصول على عدد الصلاحيات والرولز بعد التنفيذ
            $permissionsAfter = DB::table('permissions')->count();
            $rolesAfter = DB::table('roles')->count();

            $permissionsAdded = $permissionsAfter - $permissionsBefore;
            $rolesAdded = $rolesAfter - $rolesBefore;

            $output = "✅ تم تنفيذ PermissionSeeder بنجاح\n";
            $output .= "─────────────────────────────────────\n";
            $output .= "الصلاحيات: {$permissionsAfter} (تم إضافة {$permissionsAdded})\n";
            $output .= "الأدوار: {$rolesAfter} (تم إضافة {$rolesAdded})\n";

            return response()->json([
                'success' => true,
                'output' => $output,
                'permissions_count' => $permissionsAfter,
                'roles_count' => $rolesAfter,
                'permissions_added' => $permissionsAdded,
                'roles_added' => $rolesAdded,
                'message' => 'تم تنفيذ seeding للصلاحيات والرولز بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '❌ حدث خطأ: ' . $e->getMessage() . "\n\n" .
                           "تأكد من تشغيل Migrate أولاً لإنشاء الجداول المطلوبة."
            ], 500);
        }
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->session()->forget('migrate_authenticated');
        return redirect()->route('migrate.page')->with('success', 'تم تسجيل الخروج بنجاح');
    }
}

