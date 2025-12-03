<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\LanguagePhrase;

class EnglishTranslationKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create English language
        $englishLanguage = Language::where('code', 'en')->first();
        
        if (!$englishLanguage) {
            // Create English language if it doesn't exist
            $englishLanguage = Language::create([
                'name' => 'English',
                'code' => 'en',
                'status' => 'enabled',
                'is_default' => false,
            ]);
            $this->command->info('Created English language.');
        }

        $this->command->info('Adding translation keys for language: ' . $englishLanguage->name);

        // Define all translation keys in English
        $translations = [
            // Admin Panel - General
            'general_settings' => 'General Settings',
            'dashboard' => 'Dashboard',
            'products' => 'Products',
            'all_products' => 'All Products',
            'categories' => 'Categories',
            'brands' => 'Brands',
            'sliders' => 'Sliders',
            'orders' => 'Orders',
            'clients' => 'Clients',
            'reports' => 'Reports',
            'settings' => 'Settings',
            'back' => 'Back',
            'save' => 'Save',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'cancel' => 'Cancel',
            'confirm' => 'Confirm',
            'search' => 'Search',
            'add' => 'Add',
            'update' => 'Update',
            'status' => 'Status',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'yes' => 'Yes',
            'no' => 'No',
            'actions' => 'Actions',
            'name' => 'Name',
            'description' => 'Description',
            'image' => 'Image',
            'images' => 'Images',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'stock' => 'Stock',
            'sku' => 'SKU',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',

            // Admin Panel - Products
            'product_name' => 'Product Name',
            'product_description' => 'Product Description',
            'short_description' => 'Short Description',
            'sale_price' => 'Sale Price',
            'discount' => 'Discount',
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            'manage_stock' => 'Manage Stock',
            'stock_quantity' => 'Stock Quantity',
            'in_stock' => 'In Stock',
            'out_of_stock' => 'Out of Stock',
            'is_featured' => 'Featured Product',
            'add_product' => 'Add Product',
            'edit_product' => 'Edit Product',
            'delete_product' => 'Delete Product',
            'import_products' => 'Import Products',
            'export_products' => 'Export Products',
            'product_created_successfully' => 'Product created successfully',
            'product_updated_successfully' => 'Product updated successfully',
            'product_deleted_successfully' => 'Product deleted successfully',
            'product_status_updated' => 'Product status updated',

            // Admin Panel - Categories
            'category_name' => 'Category Name',
            'parent_category' => 'Parent Category',
            'add_category' => 'Add Category',
            'edit_category' => 'Edit Category',
            'delete_category' => 'Delete Category',
            'category_created_successfully' => 'Category created successfully',
            'category_updated_successfully' => 'Category updated successfully',
            'category_deleted_successfully' => 'Category deleted successfully',

            // Admin Panel - Brands
            'brand_name' => 'Brand Name',
            'add_brand' => 'Add Brand',
            'edit_brand' => 'Edit Brand',
            'delete_brand' => 'Delete Brand',
            'brand_created_successfully' => 'Brand created successfully',
            'brand_updated_successfully' => 'Brand updated successfully',
            'brand_deleted_successfully' => 'Brand deleted successfully',

            // Admin Panel - Orders
            'order_number' => 'Order Number',
            'order_date' => 'Order Date',
            'order_status' => 'Order Status',
            'payment_status' => 'Payment Status',
            'total_amount' => 'Total Amount',
            'customer_name' => 'Customer Name',
            'view_order' => 'View Order',
            'update_order_status' => 'Update Order Status',
            'order_updated_successfully' => 'Order updated successfully',

            // Admin Panel - Settings
            'appearance_settings' => 'Appearance Settings',
            'email_settings' => 'Email Settings',
            'payment_settings' => 'Payment Settings',
            'shipping_settings' => 'Shipping Settings',
            'notification_settings' => 'Notification Settings',
            'about_page' => 'About Page',
            'privacy_policy' => 'Privacy Policy',
            'terms_of_service' => 'Terms of Service',
            'refund_policy' => 'Refund Policy',
            'settings_updated_successfully' => 'Settings updated successfully',

            // Admin Panel - Languages
            'language' => 'Language',
            'languages' => 'Languages',
            'add_new_language' => 'Add New Language',
            'language_name' => 'Language Name',
            'language_code' => 'Language Code',
            'type_language_name' => 'Type language name',
            'exalmple_ar_en_fr' => 'Example: ar, en, fr',
            'language_created_successfully' => 'Language created successfully',
            'language_updated_successfully' => 'Language updated successfully',
            'language_deleted_successfully' => 'Language deleted successfully',
            'word' => 'Word',
            'translation' => 'Translation',
            'key' => 'Key',

            // Frontend - General
            'home' => 'Home',
            'shop' => 'Shop',
            'about_us' => 'About Us',
            'contact_us' => 'Contact Us',
            'categories' => 'Categories',
            'all_categories' => 'All Categories',
            'products' => 'Products',
            'all_products' => 'All Products',
            'add_to_cart' => 'Add to Cart',
            'add_to_wishlist' => 'Add to Wishlist',
            'remove_from_wishlist' => 'Remove from Wishlist',
            'view_details' => 'View Details',
            'buy_now' => 'Buy Now',
            'out_of_stock' => 'Out of Stock',
            'in_stock' => 'In Stock',
            'price' => 'Price',
            'sale_price' => 'Sale Price',
            'original_price' => 'Original Price',
            'discount' => 'Discount',
            'quantity' => 'Quantity',
            'total' => 'Total',
            'subtotal' => 'Subtotal',
            'shipping' => 'Shipping',
            'tax' => 'Tax',
            'grand_total' => 'Grand Total',
            'continue_shopping' => 'Continue Shopping',
            'proceed_to_checkout' => 'Proceed to Checkout',
            'empty_cart' => 'Empty Cart',
            'cart' => 'Cart',
            'wishlist' => 'Wishlist',
            'my_account' => 'My Account',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',
            'my_orders' => 'My Orders',
            'order_history' => 'Order History',
            'account_settings' => 'Account Settings',
            'profile' => 'Profile',
            'change_password' => 'Change Password',
            'addresses' => 'Addresses',
            'payment_methods' => 'Payment Methods',

            // Frontend - Product Details
            'product_details' => 'Product Details',
            'product_description' => 'Product Description',
            'specifications' => 'Specifications',
            'reviews' => 'Reviews',
            'write_review' => 'Write Review',
            'related_products' => 'Related Products',
            'you_may_also_like' => 'You May Also Like',
            'select_options' => 'Select Options',
            'select_color' => 'Select Color',
            'select_size' => 'Select Size',
            'add_to_cart_success' => 'Product added to cart successfully',
            'product_not_available' => 'Product not available in stock',
            'select_all_required_options' => 'Please select all required options',

            // Frontend - Contact
            'contact_form' => 'Contact Form',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'subject' => 'Subject',
            'message' => 'Message',
            'send_message' => 'Send Message',
            'message_sent_successfully' => 'Message sent successfully',
            'message_send_failed' => 'Failed to send message',
            'our_address' => 'Our Address',
            'phone_number' => 'Phone Number',
            'email_address' => 'Email Address',
            'working_hours' => 'Working Hours',

            // Frontend - Footer
            'quick_links' => 'Quick Links',
            'customer_service' => 'Customer Service',
            'newsletter' => 'Newsletter',
            'subscribe' => 'Subscribe',
            'enter_your_email' => 'Enter your email',
            'follow_us' => 'Follow Us',
            'copyright' => 'Copyright',
            'all_rights_reserved' => 'All Rights Reserved',

            // Messages
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            'info' => 'Info',
            'are_you_sure' => 'Are you sure?',
            'this_action_cannot_be_undone' => 'This action cannot be undone',
            'loading' => 'Loading...',
            'no_data_available' => 'No data available',
            'no_results_found' => 'No results found',
            'please_wait' => 'Please wait',
            'operation_completed' => 'Operation completed successfully',
            'operation_failed' => 'Operation failed',
            'invalid_data' => 'Invalid data',
            'required_field' => 'This field is required',
            'please_fill_all_required_fields' => 'Please fill all required fields',
        ];

        $added = 0;
        $updated = 0;

        foreach ($translations as $key => $word) {
            // Check if key already exists
            $existing = LanguagePhrase::where('language_id', $englishLanguage->id)
                ->where('key', $key)
                ->where('group', 'general')
                ->first();

            if ($existing) {
                // Update existing key
                $existing->update(['word' => $word]);
                $updated++;
            } else {
                // Add new key
                LanguagePhrase::create([
                    'language_id' => $englishLanguage->id,
                    'group' => 'general',
                    'key' => $key,
                    'word' => $word,
                ]);
                $added++;
            }
        }

        // Delete keys that are in database but not in translations array
        $translationKeys = array_keys($translations);
        $deleted = LanguagePhrase::where('language_id', $englishLanguage->id)
            ->where('group', 'general')
            ->whereNotIn('key', $translationKeys)
            ->delete();

        $this->command->info("English translation keys seeding completed!");
        $this->command->info("Added: {$added} keys");
        $this->command->info("Updated: {$updated} keys");
        if ($deleted > 0) {
            $this->command->info("Deleted: {$deleted} keys (not in translations array)");
        }
    }
}

