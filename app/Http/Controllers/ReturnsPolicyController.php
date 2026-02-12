<?php

namespace App\Http\Controllers;

use App\Models\ReturnsPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReturnsPolicyController extends Controller
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


        $policies = Cache::remember('returns_policies', env('DEFAULT_CACHE_VALUE'), function () {
            return ReturnsPolicy::all();
        });

        return response()->json($policies);
    }



    public function update(Request $request)
    {

        $this->checkAdmin();
        $count = ReturnsPolicy::count();

        $request->validate([
            'description' => 'string|required'
        ]);
        $description = $request->description;

        if ($count > 0) {
            $policy = ReturnsPolicy::first()->update(['description' => $description]);
        } else {
           $policy = ReturnsPolicy::create(['description' => $description]);
        }


     
        $this->ResetCache('returns_policies');
    
        return response()->json([
            'message' => 'Returns policy updated successfully',
            'policy' => $policy,
        ]);
    }
}
