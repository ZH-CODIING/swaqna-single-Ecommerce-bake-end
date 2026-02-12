<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TestimonialController extends Controller
{


    // Helper method to check admin role
    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function index()
    {

        $testimonials = Cache::remember('testimonials', env('default_cache_value'), function () {
            return Testimonial::all();
        });
        return response()->json($testimonials);
    }

    public function show($id)
    {


        $testimonial = Cache::remember('testimonial' . $id, env('default_cache_value'), function () use ($id) {
            return  Testimonial::findOrFail($id);
        });

        return response()->json($testimonial);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();  // Check admin role before allowing creation

        // في دالة store
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'review' => 'required|string',  // بدل content صار review
            'img' => 'nullable|image|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);


        if ($request->hasFile('img')) {
            $validated['img'] = $request->file('img')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create($validated);
        $this->ResetCache('testimonials');

        return response()->json([
            'message' => 'Testimonial created successfully',
            'testimonial' => $testimonial,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin(); // Check admin role before allowing update

        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'review' => 'sometimes|string',  // بدل content صار review
            'img' => 'nullable|image|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);




        if ($request->hasFile('img')) {
            if ($testimonial->img) {
                Storage::disk('public')->delete($testimonial->img);
            }
            $validated['img'] = $request->file('img')->store('testimonials', 'public');
        }

        $testimonial->update($validated);

        $this->ResetCache('testimonials');
        $this->ResetCache('testimonial' . $id);


        return response()->json([
            'message' => 'Testimonial updated successfully',
            'testimonial' => $testimonial,
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeAdmin(); // Check admin role before allowing delete

        $testimonial = Testimonial::findOrFail($id);

        if ($testimonial->img) {
            Storage::disk('public')->delete($testimonial->img);
        }

        $testimonial->delete();

        $this->ResetCache('testimonials');
        $this->ResetCache('testimonial' . $id);


        return response()->json(['message' => 'Testimonial deleted successfully']);
    }
}
