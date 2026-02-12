<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StoreInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Traits\StatsTrait;

class StoreInfoController extends Controller
{
    use StatsTrait;

    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function index()
    {
        $infos = StoreInfo::all();

        $infos->transform(function ($item) {
            $item->logo_url = $item->logo ? ('/storage/' . $item->logo) : null;
            return $item;
        });

        return response()->json($infos);
    }

    public function show($id)
    {


        $StoreInfo = StoreInfo::findOrFail($id);
        $StoreInfo->logo_url = $StoreInfo->logo ? ('/storage/' . $StoreInfo->logo) : null;




        return response()->json($StoreInfo);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_text' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:20',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'color' => 'nullable|string',


            'phone' => [
                'required',
                'string',
                'regex:/^966[0-9]{9}$/'
            ],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('store_logos', 'public');
            $validated['logo'] = $path;
        }

        $info = StoreInfo::create($validated);
        $info->logo_url = $info->logo ? ('/storage/' . $info->logo) : null;

        Cache::forget('store_infos');


        return response()->json([
            'message' => 'Store info created successfully',
            'store_info' => $info,
        ], 201);
    }

    public function HasAccess($token)
    {
        return $token == env('ACCESS_DASHBOARD_TOKEN') ? true : false;
    }

    public function update(Request $request)
    {
        $this->checkAdmin();

        $info = StoreInfo::first();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_text' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:20',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'color' => 'nullable|string',
            'zip' => 'nullable|string',


        ]);

        // إذا رفع المستخدم شعار جديد، احذف القديم وأرفع الجديد
        if ($request->hasFile('logo')) {
            // حذف الصورة القديمة لو موجودة
            if ($info->logo && Storage::disk('public')->exists($info->logo)) {
                Storage::disk('public')->delete($info->logo);
            }

            $path = $request->file('logo')->store('store_logos', 'public');
            $validated['logo'] = $path;
        }

        $info->update($validated);
        $info->logo_url = $info->logo ? ('/storage/' . $info->logo) : null;

        Cache::forget('store_infos');
        Cache::forget('store_info_' . $info->id);

        return response()->json([
            'message' => 'Store info updated successfully',
            'store_info' => $info,
        ]);
    }

    public function destroy($id)
    {
        $this->checkAdmin();

        $info = StoreInfo::findOrFail($id);

        // حذف الصورة من التخزين لو موجودة
        if ($info->logo && Storage::disk('public')->exists($info->logo)) {
            Storage::disk('public')->delete($info->logo);
        }

        $info->delete();

        Cache::forget('store_infos');
        Cache::forget('store_info_' . $id);

        return response()->json(['message' => 'Store info deleted successfully']);
    }

    public function enableStore(Request $request)
    {
        $request->validate(['token' => 'required']);
        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $store = StoreInfo::first();
        $store->update(['store_status' => 'active']);

        return response()->json([
            'message' => 'Store Enabled successfully',
        ]);
    }


    public function disableStore(Request $request)
    {
        $request->validate(['token' => 'required']);
        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $store = StoreInfo::first();
        $store->update(['store_status' => 'pending']);

        return response()->json([
            'message' => 'Store disabled successfully',
        ]);
    }


    public function updateSubscriptionPackage(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'subscription_package' => 'required|string',
            'start_subscription_date' => 'date|required'
        ]);

        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $store = StoreInfo::first();


        $store->subscription_package = null;
        $store->save();

        $store->update([
            'subscription_package' => $request->input('subscription_package'),
            'start_subscription_date' => $request->input('start_subscription_date'),
        ]);

        return response()->json([
            'message' => 'Subscription package updated successfully',
        ]);
    }


    public function StoreStats()
    {   
        
        return $this->GetStoreStats();
    }
}
