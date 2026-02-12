<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
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
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $banners = Cache::remember('banners', $cacheSeconds, function () {
            return Banner::with(['category', 'product'])->get()->map(function ($banner) {
                $banner->img_url = $banner->img ? url('storage/' . $banner->img) : null;
                return $banner;
            });
        });


        return response()->json($banners);
    }

    public function show($id)
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $banner = Cache::remember('banner' . $id, $cacheSeconds, function () use( $id ) {
            $banner = Banner::with(['category', 'product'])->findOrFail($id);
            $banner->img_url = $banner->img ? url('storage/' . $banner->img) : null;
            return $banner;
        });

        return response()->json($banner);
    }

    public function store(Request $request)
    {
        try {
            $this->checkAdmin();

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'img' => 'required|image|max:2048',
            ]);

            if ($request->hasFile('img')) {
                $validated['img'] = $request->file('img')->store('banners', 'public');
            }

            $banner = Banner::create($validated);
            $banner->img_url = $banner->img ? url('storage/' . $banner->img) : null;

            //reset caching for banners
            $this->ResetCache('banners');

            return response()->json([
                'message' => 'Banner created successfully',
                'banner' => $banner,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء إنشاء البانر.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->checkAdmin();

            $banner = Banner::findOrFail($id);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|integer|exists:categories,id',
                'product_id' => 'nullable|integer|exists:products,id',
                'img' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('img')) {
                if ($banner->img) {
                    Storage::disk('public')->delete($banner->img);
                }
                $validated['img'] = $request->file('img')->store('banners', 'public');
            }

            $banner->update($validated);
            $banner->img_url = $banner->img ? url('storage/' . $banner->img) : null;

            //reset caching for banners
            $this->ResetCache('banners');
            $this->ResetCache('banner' . $id);

            return response()->json([
                'message' => 'Banner updated successfully',
                'banner' => $banner,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث البانر.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->img) {
            Storage::disk('public')->delete($banner->img);
        }

        $this->ResetCache('banners');
        $this->ResetCache('banner' . $id);

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }

}
