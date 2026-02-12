<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\StatsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use StatsTrait;
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }


    public function index(Request $request)
    {   
        if($request->has('all')){
            $categories = Category::get();
            return response()->json($categories);
        }
        $ttl      = (int) env('DEFAULT_CACHE_VALUE', 600);
        $version  = Cache::rememberForever('categories_version', fn() => 0);


        $perPage  = 15;
        $page     = (int) $request->query('page', 1);

        $cacheKey = "categories:v{$version}:per{$perPage}:page{$page}";

        $categories = Cache::remember($cacheKey, $ttl, function () use ($perPage) {

            return Category::paginate($perPage)->through(function ($cat) {
                $cat->img_url    = $cat->img    ? url('/storage/' . $cat->img)       : null;
                $cat->banner_url = $cat->banner ? url('/storage/' . $cat->banner)    : null;
                return $cat;
            });
        });

        return response()->json($categories);
    }

    public function show($id)
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');
        $category = Cache::remember('categories_' . $id, $cacheSeconds, function () use ($id) {
            return Category::findOrFail($id);
        });
        $this->trackVisit(3, $id);    //3 Mean Category visits 

        return response()->json($category);
    }



    public function store(Request $request)
    {
        $this->checkAdmin();

        $validator = Validator::make($request->all(), [
            'img' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'name' => 'required|string|max:255',
            'name_en' => 'string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
        ], [
            'img.image' => 'يجب أن يكون الملف صورة.',
            'img.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميغابايت.',
            'banner.image' => 'يجب أن يكون البانر صورة.',
            'banner.max' => 'يجب ألا يتجاوز حجم البانر 2 ميغابايت.',
            'name.required' => 'اسم الصنف مطلوب.',
            'name.string' => 'يجب أن يكون الاسم نصًا.',
            'name.max' => 'اسم الصنف طويل جدًا (الحد الأقصى 255 حرفًا).',
            'name_en.string' => 'يجب أن يكون الاسم الانجليزى نصًا.',
            'name_en.max' => 'اسم الصنف الانجليزى طويل جدًا (الحد الأقصى 255 حرفًا).',
            'description.string' => 'يجب أن يكون الوصف نصيًا.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'حدثت أخطاء في التحقق.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('img')) {
            $validated['img'] = $request->file('img')->store('categories', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('categories', 'public');
        }

        $category = Category::create($validated);

        $this->SetIntialVersion('categories_version');


        return response()->json([
            'message' => 'تم إنشاء الصنف بنجاح.',
            'category' => $category,
        ], 201);
    }


    // تعديل فئة
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'img' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
        ]);

        if ($request->hasFile('img')) {
            if ($category->img) {
                Storage::disk('public')->delete($category->img);
            }
            $validated['img'] = $request->file('img')->store('categories', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($category->banner) {
                Storage::disk('public')->delete($category->banner);
            }
            $validated['banner'] = $request->file('banner')->store('categories', 'public');
        }

        $category->update($validated);


        $this->SetIntialVersion('categories_version');

        $this->ResetCache('categories_' . $id);


        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    // حذف فئة
    public function destroy($id)
    {
        $this->checkAdmin();

        $category = Category::findOrFail($id);

        if ($category->img) {
            Storage::disk('public')->delete($category->img);
        }
        if ($category->banner) {
            Storage::disk('public')->delete($category->banner);
        }

        $this->SetIntialVersion('categories_version');

        $this->ResetCache('categories_' . $id);

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function CategoryStats($id)
    {
        return  $this->getCategoryStats($id);

    }
}
