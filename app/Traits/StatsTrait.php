<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait StatsTrait
{
    public function trackVisit($resourceType, $resourceId): bool
    {

        //Resoucre type == 1  => store visits         -  2 => Product Visits
        return DB::table('visits')->insert([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'visited_at' => Carbon::now(),
        ]);
    }

    public function getChartData()
    {
        //1 Mean Store visits
        $storeVisits = DB::table('visits')
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->where('resource_type', 1)
            ->where('visited_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $storeVisits;
    }

    public function getProductStats($productId)
    {


        return Cache::remember("products_chart_data_{$productId}", 600, function () use ($productId) {
            $startDate = Carbon::now()->subDays(6)->startOfDay();

            //Resource Types == 2 meen products
            $visits = DB::table('visits')
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
                ->where('resource_type', 2)
                ->where('resource_id', $productId)
                ->where('visited_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $purchases = DB::table('order_items')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('product_id', $productId)
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();


            $totalVisits = DB::table('visits')
                ->where('resource_type', 2)
                ->where('resource_id', $productId)
                ->count();


            $totalSales = DB::table('order_items')
                ->where('product_id', $productId)
                ->count();

            $totalRevenue = DB::table('order_items')
                ->where('product_id', $productId)
                ->selectRaw('SUM(price * quantity) as revenue')
                ->value('revenue') ?? 0;


            $reviews = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'visits_per_day'   => $visits,
                'purchases_per_day' => $purchases,
                'total_visits'     => $totalVisits,
                'total_sales'      => $totalSales,
                'total_revenue'      => $totalRevenue,
                'reviews'            => $reviews,
            ]);
        });
    }
    public function getCategoryStats($categoryID)
    {


        return Cache::remember("category_chart_data_{$categoryID}", 600, function () use ($categoryID) {
            $startDate = Carbon::now()->subDays(6)->startOfDay();

            //Resource Types == 3 meen Categories
            $visits = DB::table('visits')
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
                ->where('resource_type', 3)
                ->where('resource_id', $categoryID)
                ->where('visited_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $totalVisits = DB::table('visits')
                ->where('resource_type', 3)
                ->where('resource_id', $categoryID)
                ->count();

            return response()->json([
                'visits_per_day'   => $visits,
                'total_visits'     => $totalVisits,
            ]);
        });
    }
    public function getBrandStats($brandID)
    {


        return Cache::remember("brand_chart_data_{$brandID}", 600, function () use ($brandID) {
            $startDate = Carbon::now()->subDays(6)->startOfDay();

            //Resource Types == 4 Mean Brands
            $visits = DB::table('visits')
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
                ->where('resource_type', 4)
                ->where('resource_id', $brandID)
                ->where('visited_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $totalVisits = DB::table('visits')
                ->where('resource_type', 4)
                ->where('resource_id', $brandID)
                ->count();

            return response()->json([
                'visits_per_day'   => $visits,
                'total_visits'     => $totalVisits,
            ]);
        });
    }
    public function GetStoreStats()
    {
        return Cache::remember('store_stats_full', 300, function () {
            $dates = collect(range(0, 6))
                ->map(fn($i) => Carbon::today()->subDays($i)->toDateString())
                ->reverse();

            $visits = DB::table('visits')
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
                ->where('resource_type', 1)
                ->where('visited_at', '>=', Carbon::today()->subDays(6))
                ->groupBy('date')
                ->pluck('count', 'date');

            $orders = DB::table('orders')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', Carbon::today()->subDays(6))
                ->groupBy('date')
                ->pluck('count', 'date');

            $revenues = DB::table('order_items')
                ->selectRaw('DATE(created_at) as date, SUM(price * quantity) as revenue')
                ->where('created_at', '>=', Carbon::today()->subDays(6))
                ->groupBy('date')
                ->pluck('revenue', 'date');

            $totalVisits = DB::table('visits')
                ->where('resource_type', 1)
                ->whereNull('resource_id')
                ->count();

            $totalOrders = DB::table('orders')->count();

            $totalRevenue = DB::table('order_items')
                ->selectRaw('SUM(price * quantity) as revenue')
                ->value('revenue') ?? 0;

            $testimonials = DB::table('testimonials')
                ->latest()
                ->take(10)
                ->get();

            return [
                'last_7_days_visits' => $dates->map(fn($date) => [
                    'date'   => $date,
                    'visits' => $visits[$date] ?? 0,
                ])->values(),

                'last_7_days_orders' => $dates->map(fn($date) => [
                    'date'   => $date,
                    'orders' => $orders[$date] ?? 0,
                ])->values(),

                'last_7_days_revenue' => $dates->map(fn($date) => [
                    'date'    => $date,
                    'revenue' => round($revenues[$date] ?? 0, 2),
                ])->values(),

                'total' => [
                    'orders'  => $totalOrders,
                    'revenue' => round($totalRevenue, 2),
                    'visits'  => $totalVisits,
                ],
                'testimonials' => $testimonials,
            ];
        });
    }
}
