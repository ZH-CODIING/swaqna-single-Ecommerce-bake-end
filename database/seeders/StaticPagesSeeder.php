<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaticPagesSeeder extends Seeder
{
    public function run(): void
    {
        // سياسة الاسترجاع
        if (DB::table('returns_policy')->count() == 0) {
            DB::table('returns_policy')->insert([
                'description' => 'يمكنك استرجاع المنتجات خلال 14 يومًا من تاريخ الاستلام.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // سياسة الخصوصية
        if (DB::table('privacy_policy')->count() == 0) {
            DB::table('privacy_policy')->insert([
                'description' => 'نحن نحترم خصوصيتك ولن نشارك معلوماتك مع أي طرف ثالث.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // معلومات الشحن
        if (DB::table('shipping_info')->count() == 0) {
            DB::table('shipping_info')->insert([
                'description' => 'نقوم بالشحن لجميع أنحاء البلاد خلال 3-7 أيام عمل.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // من نحن
        if (DB::table('about_us')->count() == 0) {
            DB::table('about_us')->insert([
                'title' => 'من نحن',
                'description' => 'نحن متجر إلكتروني متخصص في تقديم أفضل المنتجات لعملائنا.',
                'image' => 'about_us.jpg',
                'goal' => 'توفير تجربة تسوق إلكتروني سهلة وآمنة.',
                'mission' => 'تقديم منتجات عالية الجودة وخدمة عملاء ممتازة.',
                'vision' => 'أن نكون الوجهة الأولى للتسوق الإلكتروني في المنطقة.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // معلومات المتجر
        if (DB::table('store_info')->count() == 0) {
            DB::table('store_info')->insert([
                'name' => 'متجر هوت برنت',
                'description' => 'نحن نقدم خدمات طباعة احترافية.',
                'logo' => 'logo.png',
                'footer_text' => '© جميع الحقوق محفوظة لهوت برنت.',
                'phone' => '0551234567',
                'email' => 'info@hot-print.store',
                'address' => 'الرياض، المملكة العربية السعودية',
                'facebook' => 'https://facebook.com/hotprint',
                'twitter' => 'https://twitter.com/hotprint',
                'instagram' => 'https://instagram.com/hotprint',
                'youtube' => null,
                'whatsapp' => 'https://wa.me/966551234567',
                'seo_description' => 'أفضل خدمات الطباعة في السعودية.',
                'seo_keywords' => 'طباعة, مطبعة, تصميم, هوت برنت',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
