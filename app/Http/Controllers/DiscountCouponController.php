<?php

namespace App\Http\Controllers;

use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DiscountCouponController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function index()
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE', 60);

        $coupons = Cache::remember('discount_coupons', $cacheSeconds, function () {
            return DiscountCoupon::all();
        });

        return response()->json($coupons);
    }

    public function show($id)
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE', 60);

        $coupon = Cache::remember('discount_coupons_' . $id, $cacheSeconds, function () use ($id) {
            return DiscountCoupon::findOrFail($id);
        });

        return response()->json($coupon);
    }

    public function showByname($name)
    {
        $coupon = DiscountCoupon::where('code', $name)->firstOrFail();
        return response()->json($coupon);
    }

    public function store(Request $request)
    {
        try {
            $this->checkAdmin();

            $validated = $request->validate([
                'code' => 'required|string|unique:discount_coupons,code',
                'discount' => 'required|numeric|min:0|max:100',
                'end_date' => 'required|date',
                'description' => 'nullable|string',
            ], [
                'code.required' => 'يرجى إدخال كود الخصم.',
                'code.string' => 'يجب أن يكون كود الخصم نصًا.',
                'code.unique' => 'هذا الكود مستخدم من قبل.',
                'discount.required' => 'يرجى إدخال نسبة الخصم.',
                'discount.numeric' => 'يجب أن تكون نسبة الخصم رقمًا.',
                'discount.min' => 'يجب ألا تقل نسبة الخصم عن 0.',
                'discount.max' => 'يجب ألا تزيد نسبة الخصم عن 100.',
                'end_date.required' => 'يرجى إدخال تاريخ الانتهاء.',
                'end_date.date' => 'تاريخ الانتهاء يجب أن يكون بصيغة صحيحة.',
                'description.string' => 'الوصف يجب أن يكون نصًا.',
            ]);

            // Reset Cache
            Cache::forget('discount_coupons');

            $coupon = DiscountCoupon::create($validated);

            return response()->json([
                'message' => 'تم إنشاء كوبون الخصم بنجاح.',
                'coupon' => $coupon,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء إنشاء كوبون الخصم.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->checkAdmin();

            $coupon = DiscountCoupon::findOrFail($id);

            $validated = $request->validate([
                'code' => 'sometimes|required|string|unique:discount_coupons,code,' . $coupon->id,
                'discount' => 'sometimes|required|numeric|min:0|max:100',
                'end_date' => 'sometimes|required|date',
                'description' => 'nullable|string',
                'active' => 'sometimes|boolean',
            ], [
                'code.required' => 'يرجى إدخال كود الخصم.',
                'code.unique' => 'هذا الكود مستخدم من قبل.',
                'discount.required' => 'يرجى إدخال نسبة الخصم.',
                'discount.min' => 'يجب ألا تقل نسبة الخصم عن 0.',
                'discount.max' => 'يجب ألا تزيد نسبة الخصم عن 100.',
                'end_date.required' => 'يرجى إدخال تاريخ الانتهاء.',
                'end_date.date' => 'تاريخ الانتهاء يجب أن يكون بصيغة صحيحة.',
            ]);

            $coupon->update($validated);

            // Reset Cache
            Cache::forget('discount_coupons_' . $id);
            Cache::forget('discount_coupons');

            return response()->json([
                'message' => 'تم تحديث كوبون الخصم بنجاح.',
                'coupon' => $coupon,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث كوبون الخصم.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->checkAdmin();

        $coupon = DiscountCoupon::findOrFail($id);
        $coupon->delete();

        // Reset Cache
        Cache::forget('discount_coupons_' . $id);
        Cache::forget('discount_coupons');

        return response()->json(['message' => 'تم حذف كوبون الخصم بنجاح.']);
    }
}
