<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    // تحقق صلاحية الادمن، استخدمها في الدوال التي تحتاج صلاحية
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    // جلب كل المراجعات (Admins only)
    public function index()
    {
        return response()->json([]);
    }

    // جلب مراجعة محددة (Admins only)
    public function show($id)
    {
        $this->checkAdmin();

       
        $review = ProductReview::with('product')->findOrFail($id);
        

        return response()->json($review);
    }

    // إنشاء مراجعة (مفتوح للجميع)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'review' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review = ProductReview::create($validated);

        $this->SetIntialVersion('products_version');
        cache::forget('product_' . $review->product_id);
        return response()->json(['message' => 'Review created successfully', 'review' => $review], 201);
    }

    // تحديث مراجعة (Admins only)
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $review = ProductReview::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'name' => 'sometimes|string|max:255',
            'review' => 'sometimes|string',
            'rating' => 'sometimes|integer|min:1|max:5',
        ]);

        $review->update($validated);


        $this->SetIntialVersion('products_version');

        return response()->json(['message' => 'Review updated successfully', 'review' => $review]);
    }

    // حذف مراجعة (Admins only)
    public function destroy($id)
    {
        $this->checkAdmin();

        $review = ProductReview::findOrFail($id);
        $review->delete();


        $this->SetIntialVersion('products_version');

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
