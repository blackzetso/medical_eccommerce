<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\LanguagePhrase;

class TranslationKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default language
        $defaultLanguage = Language::where('is_default', true)->first();
        
        if (!$defaultLanguage) {
            $this->command->error('No default language found. Please create a default language first.');
            return;
        }

        $this->command->info('Adding translation keys for language: ' . $defaultLanguage->name);

        // Define all translation keys grouped by category
        $translations = [
            // Admin Panel - General
            'general_settings' => 'الإعدادات العامة',
            'dashboard' => 'لوحة التحكم',
            'products' => 'المنتجات',
            'all_products' => 'جميع المنتجات',
            'categories' => 'الأقسام',
            'brands' => 'العلامات التجارية',
            'sliders' => 'الشرائح',
            'orders' => 'الطلبات',
            'clients' => 'العملاء',
            'reports' => 'التقارير',
            'settings' => 'الإعدادات',
            'back' => 'رجوع',
            'save' => 'حفظ',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'cancel' => 'إلغاء',
            'confirm' => 'تأكيد',
            'search' => 'بحث',
            'add' => 'إضافة',
            'update' => 'تحديث',
            'status' => 'الحالة',
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'enabled' => 'مفعل',
            'disabled' => 'معطل',
            'yes' => 'نعم',
            'no' => 'لا',
            'actions' => 'الإجراءات',
            'name' => 'الاسم',
            'description' => 'الوصف',
            'image' => 'صورة',
            'images' => 'صور',
            'price' => 'السعر',
            'quantity' => 'الكمية',
            'stock' => 'المخزون',
            'sku' => 'رمز المنتج',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
            'sales' => 'المبيعات',
            'revenue' => 'الإيرادات',
            'inventory' => 'المخزون',
            'edit_profile' => 'تعديل الملف الشخصي',
            'help' => 'المساعدة',
            'sign_out' => 'تسجيل الخروج',

            // Admin Panel - Products
            'product_name' => 'اسم المنتج',
            'product_description' => 'وصف المنتج',
            'short_description' => 'وصف مختصر',
            'sale_price' => 'سعر البيع',
            'discount' => 'خصم',
            'discount_type' => 'نوع الخصم',
            'discount_value' => 'قيمة الخصم',
            'manage_stock' => 'إدارة المخزون',
            'stock_quantity' => 'كمية المخزون',
            'in_stock' => 'متوفر',
            'out_of_stock' => 'غير متوفر',
            'is_featured' => 'منتج مميز',
            'add_product' => 'إضافة منتج',
            'edit_product' => 'تعديل منتج',
            'delete_product' => 'حذف منتج',
            'import_products' => 'استيراد المنتجات',
            'export_products' => 'تصدير المنتجات',
            'product_created_successfully' => 'تم إنشاء المنتج بنجاح',
            'product_updated_successfully' => 'تم تحديث المنتج بنجاح',
            'product_deleted_successfully' => 'تم حذف المنتج بنجاح',
            'product_status_updated' => 'تم تحديث حالة المنتج',
            'main_image' => 'الصورة الرئيسية',
            'product_code' => 'كود المنتج',
            'weight' => 'الوزن',
            'dimensions' => 'الأبعاد',
            'slug' => 'الرابط الثابت',
            'name_en' => 'الاسم بالإنجليزية',
            'basic_information' => 'المعلومات الأساسية',
            'pricing_and_stock' => 'التسعير والمخزون',
            'no_discount' => 'بدون خصم',
            'fixed_discount' => 'خصم ثابت',
            'percentage_discount' => 'خصم نسبة مئوية',
            'discount_percentage' => 'نسبة الخصم (%)',
            'discount_amount' => 'مبلغ الخصم',
            'final_price' => 'السعر النهائي',
            'product_attributes' => 'خصائص المنتج',
            'available_attributes' => 'الخصائص المتاحة',
            'no_attributes_available' => 'لا توجد خصائص متاحة',
            'selected_attributes' => 'الخصائص المختارة',
            'price_adjustment' => 'تعديل السعر',
            'shipping_information' => 'معلومات الشحن',
            'weight_kg' => 'الوزن (كجم)',
            'dimensions_format' => 'الأبعاد (الطول × العرض × الارتفاع)',
            'classification' => 'التصنيف',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'وصف SEO',
            'add_new_product' => 'إضافة منتج جديد',
            'product_added_to_cart_success' => 'تم إضافة المنتج إلى السلة بنجاح',
            'product_added_to_favorites_success' => 'تم إضافة المنتج إلى المفضلة بنجاح',
            'product_not_available_in_stock' => 'المنتج غير متوفر في المخزون',
            'please_select_all_required_attributes' => 'يرجى تحديد جميع الخصائص المطلوبة',

            // Admin Panel - Categories
            'category_name' => 'اسم القسم',
            'parent_category' => 'القسم الرئيسي',
            'add_category' => 'إضافة قسم',
            'edit_category' => 'تعديل قسم',
            'delete_category' => 'حذف قسم',
            'category_created_successfully' => 'تم إنشاء القسم بنجاح',
            'category_updated_successfully' => 'تم تحديث القسم بنجاح',
            'category_deleted_successfully' => 'تم حذف القسم بنجاح',

            // Admin Panel - Brands
            'brand_name' => 'اسم العلامة التجارية',
            'add_brand' => 'إضافة علامة تجارية',
            'edit_brand' => 'تعديل علامة تجارية',
            'delete_brand' => 'حذف علامة تجارية',
            'brand_created_successfully' => 'تم إنشاء العلامة التجارية بنجاح',
            'brand_updated_successfully' => 'تم تحديث العلامة التجارية بنجاح',
            'brand_deleted_successfully' => 'تم حذف العلامة التجارية بنجاح',

            // Admin Panel - Orders
            'order_number' => 'رقم الطلب',
            'order_date' => 'تاريخ الطلب',
            'order_status' => 'حالة الطلب',
            'payment_status' => 'حالة الدفع',
            'total_amount' => 'المبلغ الإجمالي',
            'customer_name' => 'اسم العميل',
            'view_order' => 'عرض الطلب',
            'update_order_status' => 'تحديث حالة الطلب',
            'order_updated_successfully' => 'تم تحديث الطلب بنجاح',

            // Admin Panel - Settings
            'appearance_settings' => 'إعدادات المظهر',
            'email_settings' => 'إعدادات البريد الإلكتروني',
            'payment_settings' => 'إعدادات الدفع',
            'shipping_settings' => 'إعدادات الشحن',
            'notification_settings' => 'إعدادات الإشعارات',
            'about_page' => 'صفحة من نحن',
            'privacy_policy' => 'سياسة الخصوصية',
            'terms_of_service' => 'شروط الخدمة',
            'refund_policy' => 'سياسة الاسترجاع',
            'settings_updated_successfully' => 'تم تحديث الإعدادات بنجاح',

            // Admin Panel - Languages
            'language' => 'اللغة',
            'languages' => 'اللغات',
            'add_new_language' => 'إضافة لغة جديدة',
            'language_name' => 'اسم اللغة',
            'language_code' => 'رمز اللغة',
            'type_language_name' => 'اكتب اسم اللغة',
            'exalmple_ar_en_fr' => 'مثال: ar, en, fr',
            'language_created_successfully' => 'تم إنشاء اللغة بنجاح',
            'language_updated_successfully' => 'تم تحديث اللغة بنجاح',
            'language_deleted_successfully' => 'تم حذف اللغة بنجاح',
            'word' => 'كلمة',
            'translation' => 'ترجمة',
            'key' => 'مفتاح',

            // Frontend - General
            'home' => 'الرئيسية',
            'shop' => 'المتجر',
            'about_us' => 'من نحن',
            'contact_us' => 'اتصل بنا',
            'categories' => 'الأقسام',
            'all_categories' => 'جميع الأقسام',
            'products' => 'المنتجات',
            'all_products' => 'جميع المنتجات',
            'add_to_cart' => 'إضافة إلى السلة',
            'add_to_wishlist' => 'إضافة إلى المفضلة',
            'remove_from_wishlist' => 'إزالة من المفضلة',
            'view_details' => 'عرض التفاصيل',
            'buy_now' => 'اشتري الآن',
            'out_of_stock' => 'غير متوفر',
            'in_stock' => 'متوفر',
            'price' => 'السعر',
            'sale_price' => 'سعر البيع',
            'original_price' => 'السعر الأصلي',
            'discount' => 'خصم',
            'quantity' => 'الكمية',
            'total' => 'الإجمالي',
            'subtotal' => 'المجموع الفرعي',
            'shipping' => 'الشحن',
            'tax' => 'الضريبة',
            'grand_total' => 'المجموع الكلي',
            'continue_shopping' => 'متابعة التسوق',
            'proceed_to_checkout' => 'إتمام الطلب',
            'empty_cart' => 'السلة فارغة',
            'cart' => 'السلة',
            'wishlist' => 'المفضلة',
            'my_account' => 'حسابي',
            'login' => 'تسجيل الدخول',
            'register' => 'إنشاء حساب',
            'logout' => 'تسجيل الخروج',
            'my_orders' => 'طلباتي',
            'order_history' => 'سجل الطلبات',
            'account_settings' => 'إعدادات الحساب',
            'profile' => 'الملف الشخصي',
            'change_password' => 'تغيير كلمة المرور',
            'addresses' => 'العناوين',
            'payment_methods' => 'طرق الدفع',
            'blog' => 'المدونة',
            'my_wishlist' => 'قائمة أمنياتي',
            'username_or_email' => 'اسم المستخدم أو البريد الإلكتروني',
            'remember_me' => 'تذكرني',
            'forgot_password' => 'نسيت كلمة المرور',
            'logging_in' => 'جاري تسجيل الدخول',
            'confirm_password' => 'تأكيد كلمة المرور',
            'i_agree_to' => 'أوافق على',
            'and' => 'و',
            'already_registered' => 'مسجل بالفعل',
            'welcome_to_community' => 'مرحباً بك في المجتمع',
            'lets_learn_today' => 'هيا نتعلم اليوم',
            'clients_joined_us' => 'عملاء انضموا إلينا',
            'login_to_medical_media' => 'تسجيل الدخول إلى Medical Media',
            'nice_to_see_you' => 'سعيد برؤيتك',
            'password_min_length' => 'الحد الأدنى لطول كلمة المرور',
            'or' => 'أو',
            'dont_have_account' => 'ليس لديك حساب',
            'signup_here' => 'سجل هنا',
            'subscribe_to_newsletter' => 'اشترك في النشرة الإخبارية',
            'get_latest_info' => 'احصل على آخر المعلومات',
            'contact_info' => 'معلومات الاتصال',
            'address' => 'العنوان',
            'terms_of_use' => 'شروط الاستخدام',

            // Frontend - Product Details
            'product_details' => 'تفاصيل المنتج',
            'product_description' => 'وصف المنتج',
            'specifications' => 'المواصفات',
            'reviews' => 'التقييمات',
            'write_review' => 'اكتب تقييم',
            'related_products' => 'منتجات ذات صلة',
            'you_may_also_like' => 'قد يعجبك أيضاً',
            'select_options' => 'اختر الخيارات',
            'select_color' => 'اختر اللون',
            'select_size' => 'اختر المقاس',
            'add_to_cart_success' => 'تم إضافة المنتج إلى السلة بنجاح',
            'product_not_available' => 'المنتج غير متوفر في المخزون',
            'select_all_required_options' => 'يرجى تحديد جميع الخصائص المطلوبة',

            // Frontend - Contact
            'contact_form' => 'نموذج الاتصال',
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'subject' => 'الموضوع',
            'message' => 'الرسالة',
            'send_message' => 'إرسال الرسالة',
            'message_sent_successfully' => 'تم إرسال الرسالة بنجاح',
            'message_send_failed' => 'فشل إرسال الرسالة',
            'our_address' => 'عنواننا',
            'phone_number' => 'رقم الهاتف',
            'email_address' => 'عنوان البريد الإلكتروني',
            'working_hours' => 'ساعات العمل',

            // Frontend - Footer
            'quick_links' => 'روابط سريعة',
            'customer_service' => 'خدمة العملاء',
            'newsletter' => 'النشرة الإخبارية',
            'subscribe' => 'اشترك',
            'enter_your_email' => 'أدخل بريدك الإلكتروني',
            'follow_us' => 'تابعنا',
            'copyright' => 'حقوق النشر',
            'all_rights_reserved' => 'جميع الحقوق محفوظة',
            'designed_and_developed_by' => 'تم التصميم والبرمجة بواسطة سيمي كولن للحلول الرقمية',

            // Messages
            'success' => 'نجح',
            'error' => 'خطأ',
            'warning' => 'تحذير',
            'info' => 'معلومات',
            'are_you_sure' => 'هل أنت متأكد؟',
            'this_action_cannot_be_undone' => 'لن تتمكن من التراجع عن هذا الإجراء',
            'loading' => 'جاري التحميل...',
            'no_data_available' => 'لا توجد بيانات متاحة',
            'no_results_found' => 'لم يتم العثور على نتائج',
            'please_wait' => 'يرجى الانتظار',
            'operation_completed' => 'تمت العملية بنجاح',
            'operation_failed' => 'فشلت العملية',
            'invalid_data' => 'بيانات غير صحيحة',
            'required_field' => 'هذا الحقل مطلوب',
            'please_fill_all_required_fields' => 'يرجى ملء جميع الحقول المطلوبة',
            'error_adding_to_cart' => 'حدث خطأ أثناء إضافة المنتج إلى السلة',
            'error_adding_to_favorites' => 'حدث خطأ أثناء إضافة المنتج إلى المفضلة',
        ];

        $added = 0;
        $skipped = 0;

        foreach ($translations as $key => $word) {
            // Check if key already exists
            $exists = LanguagePhrase::where('language_id', $defaultLanguage->id)
                ->where('key', $key)
                ->where('group', 'general')
                ->exists();

            if (!$exists) {
                LanguagePhrase::create([
                    'language_id' => $defaultLanguage->id,
                    'group' => 'general',
                    'key' => $key,
                    'word' => $word,
                ]);
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("Translation keys seeding completed!");
        $this->command->info("Added: {$added} keys");
        $this->command->info("Skipped (already exist): {$skipped} keys");
    }
}

