<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function index(Request $request)
    {
        $ttl     = (int) env('DEFAULT_CACHE_VALUE', 600);
        $version = Cache::rememberForever('blogs_version', fn() => 1);


        $perPage = 15;
        $page    = (int) $request->query('page', 1);

        $cacheKey = "blogs:v{$version}:per{$perPage}:page{$page}";

        $blogs = Cache::remember($cacheKey, $ttl, function () use ($perPage) {

            return Blog::paginate($perPage)->through(function ($blog) {
                $blog->img_url = $blog->img
                    ? url('storage/' . $blog->img)
                    : null;
                return $blog;
            });
        });

        return response()->json($blogs);
    }

    public function show($id)
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $blog = Cache::remember('blog' . $id, $cacheSeconds, function () use ($id) {
            $blog = blog::findOrFail($id);
            $blog->img_url = $blog->img ? url('storage/' . $blog->img) : null;
            return $blog;
        });

        return response()->json($blog);
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
                $validated['img'] = $request->file('img')->store('blogs', 'public');
            }

            $blog = blog::create($validated);
            $blog->img_url = $blog->img ? url('storage/' . $blog->img) : null;

            $this->SetIntialVersion('blogs_version');


            return response()->json([
                'message' => 'Blog created successfully',
                'blog' => $blog,
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

            $blog = blog::findOrFail($id);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',

                'img' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('img')) {
                if ($blog->img) {
                    Storage::disk('public')->delete($blog->img);
                }
                $validated['img'] = $request->file('img')->store('blogs', 'public');
            }

            $blog->update($validated);
            $blog->img_url = $blog->img ? url('storage/' . $blog->img) : null;

            //reset caching for Blogs
           
            $this->SetIntialVersion('blogs_version');

            $this->ResetCache('blog' . $id);

            return response()->json([
                'message' => 'Blog updated successfully',
                'blog' => $blog,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث المقال.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $blog = blog::findOrFail($id);

        if ($blog->img) {
            Storage::disk('public')->delete($blog->img);
        }

      
        $this->SetIntialVersion('blogs_version');

        $this->ResetCache('blog' . $id);

        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
