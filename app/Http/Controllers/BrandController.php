<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\StatsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    use StatsTrait;
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function index(request $request)
    {   
        
        if($request->has('all')){
            $brands = Brand::get();
            return response()->json($brands);
        }

        $perPage = 15;
        $page    = (int) $request->query('page', 1);
        $ttl     = env('DEFAULT_CACHE_VALUE', 600);

        $ver     = Cache::rememberForever('brands_version', fn() => 0);
        $key     = "brands:v{$ver}:per{$perPage}:page{$page}";

        $brands = Cache::remember($key, $ttl, function () use ($perPage) {
            return Brand::paginate($perPage)->through(function ($b) {
                $b->img = $b->img ? url('/storage/' . $b->img) : null;
                return $b;
            });
        });

        return response()->json($brands);
    }

    public function show($id)
    {

        $cacheSeconds = env('DEFAULT_CACHE_VALUE');
        $brand = Cache::remember('brand_' . $id, $cacheSeconds, function () use ($id) {
            $brand = Brand::findOrFail($id);
            if ($brand->img) {
                $brand->img = url('storage/' . $brand->img);
            }
            return $brand;
        });
        $this->trackVisit(4, $id);    //4 Means Brand visits 

        return response()->json($brand);
    }

    public function store(Request $request)
    {
        try {
            $this->checkAdmin();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'img' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('img')) {
                $validated['img'] = $request->file('img')->store('brands', 'public');
            }

            $brand = Brand::create($validated);


            $this->SetIntialVersion('brands_version');


            if ($brand->img) {
                $brand->img = ('/storage/' . $brand->img);
            }

            return response()->json([
                'message' => 'Brand created successfully',
                'brand' => $brand,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء إنشاء العلامة التجارية.', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->checkAdmin();

            $brand = Brand::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'img' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('img')) {
                if ($brand->img) {
                    Storage::disk('public')->delete($brand->img);
                }
                $validated['img'] = $request->file('img')->store('brands', 'public');
            }

            $brand->update($validated);

            $this->SetIntialVersion('brands_version');

            $this->ResetCache('brand_' . $id);


            if ($brand->img) {
                $brand->img = ('/storage/' . $brand->img);
            }

            return response()->json([
                'message' => 'Brand updated successfully',
                'brand' => $brand,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء تحديث العلامة التجارية.', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->checkAdmin();

            $brand = Brand::findOrFail($id);

            if ($brand->img) {
                Storage::disk('public')->delete($brand->img);
            }

            $brand->delete();


            $this->SetIntialVersion('brands_version');

            $this->ResetCache('brand_' . $id);

            return response()->json(['message' => 'Brand deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء حذف العلامة التجارية.', 'message' => $e->getMessage()], 500);
        }
    }

    public function BrandStats($id)
    {
        return $this->getBrandStats($id);
    }
}
