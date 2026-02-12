<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AboutUsController extends Controller
{
    // دالة خاصة للتحقق من صلاحية المستخدم (مدير فقط)
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    // عرض كل البيانات
    public function index()
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $abouts = Cache::remember('about_us', $cacheSeconds, function () {
            return AboutUs::all();
        });

        return response()->json($abouts);
    }

    // عرض بيانات معينة بناءً على ID
    public function show($id)
    {

        $cacheSeconds = env('DEFAULT_CACHE_VALUE');
        $about = Cache::remember('about_us_' . $id, $cacheSeconds, function () use ($id) {
            return  AboutUs::findOrFail($id);
        });
        return response()->json($about);
    }
    // إنشاء سجل جديد
public function update(Request $request)
{
    $this->checkAdmin();

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'image|max:2048',
        'goal' => 'nullable|string',
        'mission' => 'nullable|string',
        'vision' => 'nullable|string',
    ]);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('about_us', 'public');
        $validated['image'] = '/storage/' . $path;
    }

    // تحديث أو إنشاء سجل واحد فقط
    $about = AboutUs::first(); // يجيب أول سجل
    if ($about) {
        $about->update($validated);
    } else {
        $about = AboutUs::create($validated);
    }

    // Reset cache
    $this->ResetCache('about_us');

    return response()->json([
        'message' => $about->wasRecentlyCreated ?? false
            ? 'About Us entry created successfully'
            : 'About Us entry updated successfully',
        'about' => $about,
    ], 200);
}



    // حذف سجل
    public function destroy($id)
    {
        try {
            $this->checkAdmin();

            $about = AboutUs::findOrFail($id);

            if ($about->image) {
                Storage::disk('public')->delete($about->image);
            }

            $about->delete();

            //Reset caching for about_us
            $this->ResetCache('about_us');
            $this->ResetCache('about_us_' . $id);

            return response()->json(['message' => 'About Us entry deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء حذف السجل.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
