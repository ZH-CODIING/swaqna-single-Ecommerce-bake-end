<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyUsersNewProduct;
use App\Models\Product;
use App\Services\InstgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Jobs\PostProductsToFacebook;
use App\Traits\StatsTrait;

class ProductController extends Controller
{
    use StatsTrait;
    public function getByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id'
        ]);

        $products = Product::where('category_id', $request->category_id)
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function getByBrand(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|integer|exists:brands,id'
        ]);

        $products = Product::where('brand_id', $request->brand_id)
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }
    public function filter(request $request)
    {

        $query = product::query();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }


        if ($request->filled('name_asc')) {
            $query->orderBy('name', 'asc');
        }
        if ($request->filled('name_desc')) {
            $query->orderBy('name', 'desc');
        }
        if ($request->filled('price_asc')) {
            $query->orderBy('price', 'asc');
        }
        if ($request->filled('price_desc')) {
            $query->orderBy('price', 'desc');
        }
        if ($request->filled('created_asc')) {
            $query->orderBy('created_at', 'asc');
        }
        if ($request->filled('created_desc')) {
            $query->orderBy('created_at', 'desc');
        }




        return response()->json($query->paginate(20));
    }
    // حماية الوصول فقط للمسؤولين
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    // عرض كل المنتجات مع العلاقات

    public function index(Request $request)
    {
                if($request->has('all')){
            $products = Product::get();
            return response()->json($products);
        }


        if (
            $request->filled('category_id') ||
            $request->filled('brand_id') ||
            $request->filled('max_price') ||
            $request->filled('rating') ||
            $request->filled('name_asc') ||
            $request->filled('name_desc') ||
            $request->filled('price_asc') ||
            $request->filled('price_desc') ||
            $request->filled('created_asc') ||
            $request->filled('created_desc')
        ) {
            return $this->filter($request);
        }

        $page = $request->get('page', 1);
        $cacheSeconds = env('DEFAULT_CACHE_VALUE', 600);

        $version = Cache::get('products_version', 0);


        $cacheKey = "products:v{$version}:page:{$page}";

        $products = Cache::remember($cacheKey, $cacheSeconds, function () {
            $now = now();

            $products = Product::with([
                'category:id,name,img',
                'brand:id,name,img',
            ])
                ->withAvg('reviews', 'rating')
                ->latest()
                ->paginate(15);

            $products->getCollection()->transform(function ($product) use ($now) {
                if ($product->discount_end_date && $product->discount_end_date < $now) {
                    $product->discount = 0;
                }
                $product->rating_avg = round($product->reviews_avg_rating ?? 0, 1);
                return $product;
            });
            return $products;
        });

        return response()->json($products);
    }


    // عرض منتج واحد
    public function show($id)
    {

        $product = Cache::remember('product_' . $id, env('DEFAULT_CACHE_VALUE'), function () use ($id) {
            return Product::with(['category', 'brand', 'reviews'])->findOrFail($id);
        });
        $this->trackVisit(2, $id);    //2 Mean Product visits 
        return response()->json($product);
    }

    // إنشاء منتج جديد
    public function store(Request $request)
    {
        $this->checkAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'name_en' => 'string',
            'code' => 'required|string|unique:products',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'img' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'weight' => 'required|numeric',
            'specs' => 'nullable|array',
            'quantity' => 'nullable|integer',
            'isFeatured' => 'nullable|boolean',
            'discount_end_date' => 'nullable|date',
            'rating' => 'nullable|numeric',
            'seo_keywords' => 'nullable|string',
            'seo_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $imgPath = null;
        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('products', 'public');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
        $specs = [];
        $specsInput = $request->input('specs');
        if (is_array($specsInput)) {
            foreach ($specsInput as $item) {
                if (is_string($item)) {
                    $decoded = json_decode($item, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $specs[] = $decoded;
                    }
                } elseif (is_array($item)) {
                    $specs[] = $item;
                }
            }
        }

        $product = Product::create([
            'name' => $request->name,
            'name_en' => $request->filled('name_en') ? $request->name_en : 0,
            'code' => $request->code,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'img' => $imgPath,
            'images' => $imagePaths,
            'description' => $request->description,
            'description_en' => $request->description_en,
            'discount' => $request->discount,
            'specs' => $specs,
            'quantity' => $request->filled('quantity') ? $request->quantity : 0,
            'isFeatured' => $request->isFeatured,
            'discount_end_date' => $request->discount_end_date,
            'rating' => $request->rating,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'weight' => $request->weight,
        ]);


        $this->SetIntialVersion('products_version');
        NotifyUsersNewProduct::dispatch($product);


        return response()->json([
            'product' => $product,
            'img_url' => $imgPath ? url('storage/' . $imgPath) : null,
            'images_urls' => array_map(fn($img) => url('storage/' . $img), $imagePaths),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $product = Product::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'name_en' => 'string',
            'code' => 'sometimes|string|unique:products,code,' . $product->id,
            'price' => 'sometimes|numeric',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'img' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'specs' => 'nullable|array',
            'quantity' => 'nullable|integer',
            'isFeatured' => 'nullable|boolean',
            'discount_end_date' => 'nullable|date',
            'rating' => 'nullable|numeric',
            'seo_keywords' => 'nullable|string',
            'seo_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('img')) {
            if ($product->img) {
                Storage::disk('public')->delete($product->img);
            }
            $data['img'] = $request->file('img')->store('products', 'public');
        }

             if ($request->hasFile('images')) {
            $imagePaths = [];
        
           
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        
          
            $oldImages = $product->images ?? [];
        
            
            $data['images'] = array_merge($oldImages, $imagePaths);
        }

        if (isset($data['specs'])) {
            $specsInput = $data['specs'];
            $specs = [];

            foreach ($specsInput as $item) {
                if (is_string($item)) {
                    $decoded = json_decode($item, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $specs[] = $decoded;
                    }
                } elseif (is_array($item)) {
                    $specs[] = $item;
                }
            }

            $data['specs'] = $specs;
        }

        $product->update($data);


        $this->SetIntialVersion('products_version');

        $this->ResetCache('product_' . $id);


        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
            'img_url' => $product->img ? url('storage/' . $product->img) : null,
            'images_urls' => $product->images ? array_map(fn($img) => url('storage/' . $img), $product->images) : [],
        ]);
    }



    public function destroy($id)
    {
        $this->checkAdmin();

        $product = Product::findOrFail($id);

        if ($product->img) {
            Storage::disk('public')->delete($product->img);
        }

        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $this->SetIntialVersion('products_version');

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    public function GetRecommendedProducts()
    {
        $topProducts = Cache::remember('top_rated_products', 3600, function () {
            return Product::withCount([
                'reviews as average_rating' => function ($query) {
                    $query->select(DB::raw('coalesce(avg(rating),0)'));
                }
            ])
                ->orderByDesc('average_rating')
                ->take(10)
                ->get();
        });

        return response()->json(['status' => 'ok', 'recommended_products' => $topProducts], 200);
    }
    public function GetTopProducts()
    {
        $topProducts = Cache::remember('top_sold_products', 3600, function () {
            return DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.price',
                    'products.img',
                    'products.quantity',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->groupBy('products.id', 'products.name', 'products.price', 'products.img', 'products.quantity')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();
        });

        return response()->json(['status' => 'ok', 'topProducts' => $topProducts], 200);
    }
    public function PublishProductsTofacebook()
    {
        PostProductsToFacebook::dispatch();
        return response()->json(['status' => 'processing'], 200);
    }


    public function PublishProductsToInstaGram()
    {
        $products = Product::with(['category', 'brand', 'reviews'])->get();
        $insta_service = new InstgramService();

        foreach ($products as $product) {
            $caption = "**{$product->name}**\n\n";

            $imageUrl = asset('storage/' . $product->img);

            $insta_service->MakePost($imageUrl, $caption, null, null);
        }

        return response()->json(['status' => 'Done'], 200);
    }

    public function GetSaleProducts()
    {
        $products = Product::whereNotNull('discount_end_date')
            ->where('discount_end_date', '>', now())
            ->get();

        return response()->json(['status' => 'ok', 'SaleProducts' => $products], 200);
    }

    public function ProductStats($id)
    {
        return $this->getProductStats($id);
    }
}
