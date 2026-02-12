<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PrivacyPolicyController extends Controller
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


        $policies = Cache::remember('privacy_policies', env('DEFAULT_CACHE_VALUE'), function () {
            return PrivacyPolicy::all();
        });

        return response()->json($policies);
    }

    public function show($id)
    {

        $policy = Cache::remember('privacy_policy_' . $id, env('DEFAULT_CACHE_VALUE'), function () use ($id) {
            return PrivacyPolicy::findOrFail($id);
        });

        return response()->json($policy);
    }



    public function updateOrCreate(Request $request)
    {

     

        $validated = $request->validate([
            'description' => 'required|string|max:255',
        ]);


        $policy = PrivacyPolicy::updateOrCreate(
            ['id' => 1],
            $validated
        );

        $this->ResetCache('privacy_policies');
        $this->ResetCache('privacy_policy_' . 1);

        return response()->json([
            'message' => $policy->wasRecentlyCreated
                ? 'Privacy policy created successfully'
                : 'Privacy policy updated successfully',
            'policy' => $policy,
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->checkAdmin();

            $policy = PrivacyPolicy::findOrFail($id);
            $policy->delete();

            $this->ResetCache('privacy_policies');
            $this->ResetCache('privacy_policy_' . $id);


            return response()->json(['message' => 'Privacy policy deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}
