<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OfferBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class OfferBannerController extends Controller
{
    // حماية الوصول فقط للمسؤولين
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

public function ResetCache($key)
{
    Cache::forget($key);
}



    public function index()
    {
        // حذف العروض المنتهية قبل إرجاع البيانات
        OfferBanner::whereNotNull('end_date')
            ->where('end_date', '<', Carbon::now())
            ->each(function ($banner) {
                if ($banner->img) {
                    Storage::disk('public')->delete($banner->img);
                }
                $banner->delete();
            });

        $banners = Cache::remember('offer_banners', 1, function () {
            return OfferBanner::with(['category', 'product'])->get()->map(function ($banner) {
                // If img already starts with http:// or https://, keep it as‑is;
                // otherwise build a URL on the public disk.
                $banner->img_url = Str::startsWith($banner->img, ['http://', 'https://'])
                    ? $banner->img                                   // absolute URL stored in DB
                    : Storage::disk('public')->url(ltrim($banner->img, '/'));  // relative path

                return $banner;
            });
        });

        return response()->json($banners);
    }


    public function show($id)
    {
        $banner =
             $cacheSeconds = env('DEFAULT_CACHE_VALUE', 60); 
        $banner = Cache::remember('offer_banner_' . $id, $cacheSeconds, function () use ($id) {
            return OfferBanner::with(['category', 'product'])->findOrFail($id);
        });
        return response()->json($banner);
    }

public function store(Request $request)
{
    $this->checkAdmin();

$validator = Validator::make($request->all(), [
    'title' => 'nullable|string|max:255',
    'description' => 'nullable|string',
    'category_id' => 'nullable|exists:categories,id',
    'product_id' => 'nullable|exists:products,id',
    'end_date' => 'nullable|date|after_or_equal:today',
    'discount' => 'nullable|numeric|min:0',
    'img' => 'required|image|max:2048',
], [
    'title.string' => 'العنوان يجب أن يكون نص.',
    'title.max' => 'العنوان لا يجب أن يتجاوز 255 حرف.',
    'description.string' => 'الوصف يجب أن يكون نص.',
    'category_id.exists' => 'القسم غير موجود.',
    'product_id.exists' => 'المنتج غير موجود.',
    'end_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخ صحيح.',
    'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون اليوم أو بعده.',
    'discount.numeric' => 'الخصم يجب أن يكون رقم.',
    'discount.min' => 'الخصم لا يمكن أن يكون أقل من 0.',
    'img.required' => 'الصورة مطلوبة.',
    'img.image' => 'الملف يجب أن يكون صورة.',
    'img.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجا.',
]);


    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();

    if ($request->hasFile('img')) {
        $validated['img'] = $request->file('img')->store('offer_banners', 'public');
    }

    $banner = OfferBanner::create($validated);

    $this->ResetCache('offer_banners');

    return response()->json([
        'message' => 'تم إنشاء البانر بنجاح',
        'offer_banner' => $banner,
    ], 201);
}

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $banner = OfferBanner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'product_id' => 'nullable|exists:products,id',
            'end_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'img' => 'nullable|image|max:2048',

        ]);

        if ($request->hasFile('img')) {
            if ($banner->img) {
                Storage::disk('public')->delete($banner->img);
            }
            $validated['img'] = $request->file('img')->store('offer_banners', 'public');
        }

        $banner->update($validated);

        $this->ResetCache('offer_banners');
        $this->ResetCache('offer_banner_' . $id);


        return response()->json([
            'message' => 'Offer banner updated successfully',
            'offer_banner' => $banner,
        ]);
    }

    public function destroy($id)
    {
        $this->checkAdmin();

        $banner = OfferBanner::findOrFail($id);

        if ($banner->img) {
            Storage::disk('public')->delete($banner->img);
        }

        $banner->delete();

        $this->ResetCache('offer_banners');
        $this->ResetCache('offer_banner_' . $id);

        return response()->json(['message' => 'Offer banner deleted successfully']);
    }
}
