<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StoreInfo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Exception;

class ImportController extends Controller
{
    public function importStores(Request $request)
    {
        $file = $request->file('excel_file');

        if (!$file) {
            return response()->json(['message' => 'Please upload an Excel file'], 400);
        }

        // تحويل Excel لمصفوفة
        // سيتم تحويل عناوين الأعمدة تلقائيًا إلى حروف صغيرة
        // مع استخدام headers: false لقراءة البيانات بفهرس العمود
        $data = Excel::toArray(new \stdClass(), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
            'headers' => false,
        ]);

        if (empty($data[0])) {
            return response()->json(['message' => 'The uploaded file is empty or has no data.'], 400);
        }
        
        $imported_count = 0;
        $failed_rows = [];

        DB::beginTransaction();

        try {
            foreach ($data[0] as $index => $row) {
                // تخطي الصف الأول الذي يحتوي على عناوين الأعمدة
                if ($index == 0) {
                    continue;
                }

                // التحقق من وجود عمود البريد الإلكتروني (العمود رقم 2)
                if (!isset($row[2]) || is_null($row[2])) {
                    $failed_rows[] = ['row' => $index + 1, 'reason' => 'Email is missing.'];
                    continue;
                }

                // تحويل تاريخ الاشتراك إلى تنسيق صحيح
                $startSubscriptionDate = null;
                if (isset($row[20])) {
                    $startSubscriptionDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[20])->format('Y-m-d');
                }

                // إنشاء مستخدم
                User::create([
                    'name'     => $row[0] ?? 'N/A', // Merchant Name
                    'phone'    => $row[1] ?? null,  // Phone Num
                    'email'    => $row[2],          // Email
                    'password' => $row[3] ?? null,  // Password
                    'location' => $row[14] ?? null, // City
                    'role'     => 'admin', // تم إضافة هذا الحقل لتعيين الدور
                ]);

                // إنشاء معلومات المتجر
                StoreInfo::create([
                    'name'                 => $row[0] ?? 'N/A',
                    'email'                => $row[2] ?? null,
                    'phone'                => $row[1] ?? null,
                    'logo'                 => $row[4] ?? null, // Commercial Register
                    'address'              => $row[15] ?? null, // Street
                    'city'                 => $row[14] ?? null, // City
                    'area'                 => $row[16] ?? null, // Area
                    'zip'                  => $row[19] ?? null, // ZIP
                    'subscription_package' => $row[6] ?? null,
                    'subscription_duration' => $row[7] ?? null,
                    'start_subscription_date' => $startSubscriptionDate,
                    'store_status'         => $row[8] ?? 'pending',
                    'tax_number'           => $row[11] ?? null,
                    'commercial_register'  => $row[4] ?? null,
                    'national_id'          => $row[5] ?? null,
                    'iban_number'          => $row[12] ?? null,
                    'domain'               => $row[17] ?? null,
                    'theme'                => $row[18] ?? null,
                    'registration_date'    => $row[9] ?? null,
                    'birth_date'           => $row[10] ?? null,
                    'description'          => '',
                    'footer_text'          => '',
                    'location_id'          => null, // قيمة افتراضية
                    'facebook'             => null,
                    'twitter'              => null,
                    'instagram'            => null,
                    'youtube'              => null,
                    'whatsapp'             => null,
                ]);

                $imported_count++;
            }
            DB::commit();
            return response()->json([
                'message' => 'Data imported successfully',
                'imported_rows' => $imported_count,
                'failed_rows' => $failed_rows,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
